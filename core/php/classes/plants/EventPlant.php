<?php
/**
 * Live shows. Parties. Good times.
 *
 * @package seed.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2010, CASH Music
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
		$query = "INSERT INTO live_venues (name,address1,address2,city,region,country,postalcode,website,phone) VALUES ('$name','$address1','$address2','$city','$region','$country','$postalcode','$website','$phone')";
		if ($this->db->doQuery($query)) {
			$new_venue_id = mysql_insert_id();
			return $new_venue_id;
		} else {
			//return MySQL error?
			return false;
		}
	}

	public function addDate($date,$user_id,$venue_id,$publish,$cancelled,$comment) {
		$query = "INSERT INTO live_events (date,user_id,venue_id,publish,cancelled,comments) VALUES ($date,$user_id,$venue_id,$publish,$cancelled,'$comment')";
		if ($this->db->doQuery($query)) {
			$new_venue_id = mysql_insert_id();
			return $new_venue_id;
		} else {
			return false;
		}
	}

	public function getAllDates($user_id,$offset=0) {
		$offset = 86400 * $offset;
		$cutoffdate = time() - $offset;
		$query = "SELECT d.id,u.display_name as user_display_name,d.date,v.name as venuename,v.address1,v.address2,v.city,v.region,v.country,v.postalcode,v.website,v.phone,d.publish,d.cancelled,d.comments FROM live_events d JOIN live_venues v ON d.venue_id = v.id JOIN seed_users u ON d.user_id = u.id WHERE d.date > $cutoffdate AND u.id = $user_id ORDER BY d.date ASC";
		return $this->db->doQueryForMultiAssoc($query);
	}

	public function getDatesBetween($user_id,$afterdate,$beforedate) {
		$query = "SELECT d.id,u.display_name as user_display_name,d.date,v.name as venuename,v.address1,v.address2,v.city,v.region,v.country,v.postalcode,v.website,v.phone,d.publish,d.cancelled,d.comments FROM live_events d JOIN live_venues v ON d.venue_id = v.id JOIN seed_users u ON d.user_id = u.id WHERE d.date > $afterdate AND d.date < $beforedate AND u.id = $user_id ORDER BY d.date ASC";
		return $this->db->doQueryForMultiAssoc($query);
	}

	public function getDatesByArtistAndDate($user_id,$date) {
		$query = "SELECT d.id,u.display_name as user_display_name,d.date,v.name as venuename,v.address1,v.address2,v.city,v.region,v.country,v.postalcode,v.website,v.phone,d.publish,d.cancelled,d.comments FROM live_events d JOIN live_venues v ON d.venue_id = v.id JOIN seed_users u ON d.user_id = u.id WHERE d.date = $date AND u.id = $user_id ORDER BY d.date ASC";
		return $this->db->doQueryForMultiAssoc($query);
	}

	public function getVenueById($venue_id) {
		$query = "SELECT * FROM live_venues WHERE id = $venue_id";
		return $this->db->doQueryForAssoc($query);
	}

} // END class 
?>