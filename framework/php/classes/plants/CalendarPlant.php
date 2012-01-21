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
			$this->routing_table = array(
				// alphabetical for ease of reading
				// first value  = target method to call
				// second value = allowed request methods (string or array of strings)
				'addevent'          => array('addEvent','direct'),
				'addvenue'          => array('addVenue','direct'),
				'deletevenue'       => array('deleteVenue','direct'),
				'editevent'         => array('editEvent','direct'),
				'editvenue'         => array('editVenue','direct'),
				'getallvenues'      => array('getAllVenues','direct'),
				'geteventsbetween'  => array('getDatesBetween','direct'),
				'getevent'          => array('getEvent','direct'),
				'getvenue'          => array('getVenue','direct')
			);
			// see if the action matches the routing table:
			$basic_routing = $this->routeBasicRequest();
			if ($basic_routing !== false) {
				return $basic_routing;
			} else {
				switch ($this->action) {
					case 'getevents':
						if (!$this->checkRequestMethodFor('direct')) { return $this->pushFailure('request method not allowed'); }
						if (!$this->requireParameters('user_id','visible_event_types')) { return $this->pushFailure('missing required parameter'); }
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
					default:
						return $this->response->pushResponse(
							400,$this->request_type,$this->action,
							$this->request,
							'unknown action'
						);
				}
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
	
	protected function addVenue($name,$city,$address1='',$address2='',$region='',$country='',$postalcode='',$url='',$phone='') {
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

	protected function editVenue($venue_id,$name,$address1,$address2,$city,$region,$country,$postalcode,$url,$phone) {
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

	protected function deleteVenue($venue_id) {
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

	protected function addEvent($date,$user_id,$venue_id,$purchase_url='',$comment='',$published=0,$cancelled=0) {
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

	protected function editEvent($date,$event_id,$venue_id,$purchase_url,$comment,$published,$cancelled) {
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

	protected function getDatesBetween($user_id,$offset=0,$cutoff_date_low='now',$cancelled_status=0,$published_status=1,$cutoff_date_high=2051244000) {
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

	protected function getEvent($event_id) {
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

	protected function getVenue($venue_id) {
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

	protected function getAllVenues() {
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