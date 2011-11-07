<?php
/**
 * Live shows. Parties. Good times. CalendarPlant will undergo additional changes 
 * by the time the platform reaches 1.0 release.
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class CalendarPlant extends PlantBase {
	public function __construct($request_type,$request) {
		$this->request_type = 'calendar';
		$this->plantPrep($request_type,$request);
	}
	
	public function processRequest() {
		if ($this->action) {
			switch ($this->action) {
				case 'addvenue':
					if (!$this->requireParameters('name','city')) { return $this->sessionGetLastResponse(); }
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					$addvenue_address1 = '';
					$addvenue_address2 = '';
					$addvenue_region = '';
					$addvenue_country = 'USA';
					$addvenue_postalcode = '';
					$addvenue_url = '';
					$addvenue_phone = '';
					if (isset($this->request['address1'])) { $addvenue_address1 = $this->request['address1']; }
					if (isset($this->request['address2'])) { $addvenue_address2 = $this->request['address2']; }
					if (isset($this->request['region'])) { $addvenue_region = $this->request['region']; }
					if (isset($this->request['country'])) { $addvenue_country = $this->request['country']; }
					if (isset($this->request['postalcode'])) { $addvenue_postalcode = $this->request['postalcode']; }
					if (isset($this->request['url'])) { $addvenue_url = $this->request['url']; }
					if (isset($this->request['phone'])) { $addvenue_phone = $this->request['phone']; }
					$result = $this->addVenue($this->request['name'],$addvenue_address1,$addvenue_address2,$this->request['city'],$addvenue_region,$addvenue_country,$addvenue_postalcode,$addvenue_url,$addvenue_phone);
					if ($result) {
						return $this->pushSuccess($result,'Venue added. Id in payload.');
					} else {
						return $this->pushFailure('there was an error adding the venue');
					}
					break;
				case 'editvenue':
					if (!$this->requireParameters('id','name','city')) { return $this->sessionGetLastResponse(); }
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					$result = $this->editVenue($this->request['id'],$this->request['name'],$this->request['address1'],$this->request['address2'],$this->request['city'],$this->request['region'],$this->request['country'],$this->request['postalcode'],$this->request['url'],$this->request['phone']);
					if ($result) {
						return $this->pushSuccess($result,'Venue added. Id in payload.');
					} else {
						return $this->pushFailure('there was an error adding the venue');
					}
					break;
				case 'deletevenue':
					if (!$this->requireParameters('id')) { return $this->sessionGetLastResponse(); }
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					$result = $this->deleteVenue($this->request['id']);
					if ($result) {
						return $this->pushSuccess($result,'success. deleted');
					} else {
						return $this->pushFailure('there was an error deleting the venue');
					}
					break;
				case 'addevent':
					if (!$this->requireParameters('date','user_id','venue_id')) { return $this->sessionGetLastResponse(); }
					$addevent_purchase_url = '';
					$addevent_comment = '';
					$addevent_published = 0;
					$addevent_cancelled = 0;
					if (isset($this->request['purchase_url'])) { $addevent_purchase_url = $this->request['purchase_url']; }
					if (isset($this->request['comment'])) { $addevent_comment = $this->request['comment']; }
					if (isset($this->request['published'])) { $addevent_published = $this->request['published']; }
					if (isset($this->request['cancelled'])) { $addevent_cancelled = $this->request['cancelled']; }
					$result = $this->addEvent($this->request['date'],$this->request['user_id'],$this->request['venue_id'],$addevent_purchase_url,$addevent_comment,$addevent_published,$addevent_cancelled);
					if ($result) {
						return $this->pushSuccess($result,'Event added. Id in payload.');
					} else {
						return $this->pushFailure('there was an error adding the event');
					}
					break;
				case 'editevent':
					if (!$this->requireParameters('date','event_id','venue_id')) { return $this->sessionGetLastResponse(); }
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					$addevent_purchase_url = '';
					$addevent_comment = '';
					$addevent_published = 0;
					$addevent_cancelled = 0;
					if (isset($this->request['purchase_url'])) { $addevent_purchase_url = $this->request['purchase_url']; }
					if (isset($this->request['comment'])) { $addevent_comment = $this->request['comment']; }
					if (isset($this->request['published'])) { $addevent_published = $this->request['published']; }
					if (isset($this->request['cancelled'])) { $addevent_cancelled = $this->request['cancelled']; }
					$result = $this->editEvent($this->request['date'],$this->request['event_id'],$this->request['venue_id'],$addevent_purchase_url,$addevent_comment,$addevent_published,$addevent_cancelled);
					if ($result) {
						return $this->pushSuccess($result,'Event edited.');
					} else {
						return $this->pushFailure('there was an error');
					}
					break;
				case 'getevent':
					if (!$this->requireParameters('event_id')) { return $this->sessionGetLastResponse(); }
					$result = $this->getEventById($this->request['event_id']);
					if ($result) {
						return $this->pushSuccess($result,'Event information in payload.');
					} else {
						return $this->pushFailure('could not find event');
					}
					break;
				case 'getevents':
					if (!$this->requireParameters('user_id','visible_event_types')) { return $this->sessionGetLastResponse(); }
					$offset = 0;
					$published_status = 1;
					$cancelled_status = 0; // need to find a way to set this to a wildcard. '*' doesn't work for sqlite, but does for mysql
					switch ($this->request['visible_event_types']) {
						case 'upcoming':
							$cutoff_date_low = 'now';
							$cutoff_date_high = 2051244000;
							break;
						case 'archive':
							$cutoff_date_low = 229305600; // april 8, 1977 -> yes it's significant
							$cutoff_date_high = 'now';
							break;
						case 'both':
							$cutoff_date_low = 229305600;
							$cutoff_date_high = 2051244000;
							break;
					}
					if (isset($this->request['offset'])) { $offset = $this->request['offset']; }
					if (isset($this->request['published_status'])) { $published_status = $this->request['published_status']; }
					if (isset($this->request['cancelled_status'])) { $cancelled_status = $this->request['cancelled_status']; }
					$result = $this->getDatesBetween($this->request['user_id'],$offset,$cutoff_date_low,$cancelled_status,$published_status,$cutoff_date_high);
					if ($result) {
						return $this->pushSuccess($result,'Success. Array of events in payload.');
					} else {
						return $this->pushFailure('No tourdates were found matching your criteria.');
					}
					break;
				case 'geteventsbetween':
					if (!$this->requireParameters('user_id','cutoff_date_low','cutoff_date_high')) { return $this->sessionGetLastResponse(); }
					$offset = 0;
					$published_status = 1;
					$cancelled_status = 0; // need to find a way to set this to a wildcard. '*' doesn't work for sqlite, but does for mysql
					if (isset($this->request['offset'])) { $offset = $this->request['offset']; }
					if (isset($this->request['published_status'])) { $published_status = $this->request['published_status']; }
					if (isset($this->request['cancelled_status'])) { $cancelled_status = $this->request['cancelled_status']; }
					$result = $this->getDatesBetween($this->request['user_id'],$offset,$this->request['cutoff_date_low'],$cancelled_status,$published_status,$this->request['cutoff_date_high']);
					if ($result) {
						return $this->pushSuccess($result,'Success. Array of events in payload.');
					} else {
						return $this->pushFailure('No tourdates were found matching your criteria.');
					}
					break;
				case 'getvenue':
					if (!$this->requireParameters('id')) { return $this->sessionGetLastResponse(); }
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					$result = $this->getVenueById($this->request['id']);
					if ($result) {
						return $this->pushSuccess($result,'Success. Venue information in payload.');
					} else {
						return $this->pushFailure('There was an error.');
					}
					break;
				case 'getallvenues':
					if (!$this->checkRequestMethodFor('direct')) return $this->sessionGetLastResponse();
					$result = $this->getAllVenues();
					if ($result) {
						return $this->pushSuccess($result,'Success. Known venues in payload.');
					} else {
						return $this->pushFailure('There was an error.');
					}
					break;
				default:
					return $this->response->pushResponse(
						400,$this->request_type,$this->action,
						$this->request,
						'unknown action'
					);
			}
		} else {
			return $this->response->pushResponse(
				400,
				$this->request_type,
				$this->action,
				$this->request,
				'no action specified'
			);
		}
	}
	
	public function addVenue($name,$address1,$address2,$city,$region,$country,$postalcode,$url,$phone) {
		$result = $this->db->setData(
			'venues',
			array(
				'name' => $name,
				'address1' => $address1,
				'address2' => $address2,
				'city' => $city,
				'region' => $region,
				'country' => $country,
				'postalcode' => $postalcode,
				'url' => $url,
				'phone' => $phone
			)
		);
		return $result;
	}

	public function editVenue($venue_id,$name,$address1,$address2,$city,$region,$country,$postalcode,$url,$phone) {
		$result = $this->db->setData(
			'venues',
			array(
				'name' => $name,
				'address1' => $address1,
				'address2' => $address2,
				'city' => $city,
				'region' => $region,
				'country' => $country,
				'postalcode' => $postalcode,
				'url' => $url,
				'phone' => $phone
			),
			array(
				"id" => array(
					"condition" => "=",
					"value" => $venue_id
				)
			)
		);
		return $result;
	}

	public function deleteVenue($venue_id) {
		$result = $this->db->deleteData(
			'venues',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $venue_id
				)
			)
		);
		return $result;
	}

	public function addEvent($date,$user_id,$venue_id,$purchase_url,$comment,$published,$cancelled) {
		$result = $this->db->setData(
			'events',
			array(
				'date' => $date,
				'user_id' => $user_id,
				'venue_id' => $venue_id,
				'published' => $published,
				'cancelled' => $cancelled,
				'purchase_url' => $purchase_url,
				'comments' => $comment
			)
		);
		return $result;
	}

	public function editEvent($date,$event_id,$venue_id,$purchase_url,$comment,$published,$cancelled) {
		$result = $this->db->setData(
			'events',
			array(
				'date' => $date,
				'venue_id' => $venue_id,
				'published' => $published,
				'cancelled' => $cancelled,
				'purchase_url' => $purchase_url,
				'comments' => $comment
			),
			array(
				"id" => array(
					"condition" => "=",
					"value" => $event_id
				)
			)
		);
		return $result;
	}

	public function getDatesBetween($user_id,$offset=0,$cutoff_date_low='now',$cancelled_status=0,$published_status=1,$cutoff_date_high=2051244000) {
		// offset = allow dates to hang around for x days after they've passed
		// beforedate=2051244000 = jan 1, 2035. don't book dates that far in advance, jerks
		$offset = 86400 * $offset;
		if ($cutoff_date_low == 'now') {
			$cutoff_date_low = time() - $offset;
		}
		if ($cutoff_date_high == 'now') {
			$cutoff_date_high = time() + $offset;
		}
		$result = $this->db->getData(
			'CalendarPlant_getDatesBetween',
			false,
			array(
				"user_id" => array(
					"condition" => "=",
					"value" => $user_id
				),
				"cutoff_date_low" => array(
					"condition" => ">",
					"value" => $cutoff_date_low
				),
				"cutoff_date_high" => array(
					"condition" => "<",
					"value" => $cutoff_date_high
				),
				"cancelled_status" => array(
					"condition" => "=",
					"value" => $cancelled_status
				),
				"published_status" => array(
					"condition" => "=",
					"value" => $published_status
				)
			)
		);
		return $result;
	}

	public function getEventById($event_id) {
		$result = $this->db->getData(
			'CalendarPlant_getEventById',
			false,
			array(
				"event_id" => array(
					"condition" => "=",
					"value" => $event_id
				)
			)
		);
		return $result[0];
	}

	public function getVenueById($venue_id) {
		$result = $this->db->getData(
			'venues',
			'*',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $venue_id
				)
			)
		);
		return $result[0];
	}

	public function getAllVenues() {
		$result = $this->db->getData(
			'venues',
			'*',
			false,
			false,
			'name ASC'
		);
		return $result;
	}

} // END class 
?>