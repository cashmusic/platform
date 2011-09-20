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
						return $this->pushSuccess($result,'Venue added. Id in payload.');
					} else {
						return $this->pushFailure('there was an error adding the venue');
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

	public function getAllDates($user_id,$offset=0) {
		$offset = 86400 * $offset;
		$cutoffdate = time() - $offset;
		$result = $this->db->doSpecialQuery(
			'CalendarPlant_getAllDates',
			array('user_id' => $user_id,'cutoffdate' => $cutoffdate)
		);
		return $result;
	}

	public function getDatesBetween($user_id,$afterdate,$beforedate) {
		$result = $this->db->doSpecialQuery(
			'CalendarPlant_getDatesBetween',
			array('user_id' => $user_id,'afterdate' => $afterdate,'beforedate' => $beforedate)
		);
		return $result;
	}

	public function getDatesByArtistAndDate($user_id,$date) {
		$result = $this->db->doSpecialQuery(
			'CalendarPlant_getDatesByArtistAndDate',
			array('user_id' => $user_id,'date' => $date)
		);
		return $result;
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

} // END class 
?>