<?php
/**
 * Live shows. Parties. Good times.
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
class EventPlant extends PlantBase {
	public function __construct($request_type,$request) {
		$this->request_type = 'event';
		$this->plantPrep($request_type,$request);
	}
	
	public function processRequest() {
		$result = $this->response->pushResponse(200,$this->request_type,$this->action,$this->request,'no context for a simple echo');
		return $result;
	}
	
	public function addVenue($name,$address1,$address2,$city,$region,$country,$postalcode,$website,$phone) {
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
				'website' => $website,
				'phone' => $phone
			)
		);
		return $result;
	}

	public function addDate($date,$user_id,$venue_id,$publish,$cancelled,$comment) {
		$result = $this->db->setData(
			'events',
			array(
				'date' => $date,
				'user_id' => $user_id,
				'venue_id' => $venue_id,
				'publish' => $publish,
				'cancelled' => $cancelled,
				'comments' => $comment
			)
		);
		return $result;
	}

	public function getAllDates($user_id,$offset=0) {
		$offset = 86400 * $offset;
		$cutoffdate = time() - $offset;
		$result = $this->db->doSpecialQuery(
			'EventPlant_getAllDates',
			array('user_id' => $user_id,'cutoffdate' => $cutoffdate)
		);
		return $result;
	}

	public function getDatesBetween($user_id,$afterdate,$beforedate) {
		$result = $this->db->doSpecialQuery(
			'EventPlant_getDatesBetween',
			array('user_id' => $user_id,'afterdate' => $afterdate,'beforedate' => $beforedate)
		);
		return $result;
	}

	public function getDatesByArtistAndDate($user_id,$date) {
		$result = $this->db->doSpecialQuery(
			'EventPlant_getDatesByArtistAndDate',
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