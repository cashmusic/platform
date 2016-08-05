<?php
/**
 * Live shows. Parties. Good times. CalendarPlant will undergo additional changes
 * by the time the platform reaches 1.0 release.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 *
 *
 * This file is generously sponsored by Christine Hughes, with an all-consuming passion in lockstep
 *
 **/
class CalendarPlant extends PlantBase {
	public function __construct($request_type,$request) {
		$this->request_type = 'calendar';
		$this->venues_api = CASH_VENUES_API;

		$this->routing_table = array(
			// alphabetical for ease of reading
			// first value  = target method to call
			// second value = allowed request methods (string or array of strings)
			'addevent'          => array('addEvent','direct'),
			'addvenue'          => array('addVenue','direct'),
			'deleteevent'       => array('deleteEvent','direct'),
			'deletevenue'       => array('deleteVenue','direct'),
			'editevent'         => array('editEvent','direct'),
			'editvenue'         => array('editVenue','direct'),
			'findvenues'        => array('findVenues','direct'),
			'getallvenues'      => array('getAllVenues','direct'),
			'getevent'          => array('getEvent','direct'),
			'getevents'         => array('getEvents','direct'),
			'getvenue'          => array('getVenue','direct')
		);


		$this->plantPrep($request_type,$request);
	}

	protected function findVenues($query,$page=1,$max_returned=12) {
		$limit = (($page - 1) * $max_returned) . ',' . $max_returned;
		$fuzzy_query = '%' . $query . '%';

		$result = $this->db->getData(
			'CalendarPlant_findVenues',
			false,
			array(
				"query" => array(
					"condition" => "=",
					"value" => $fuzzy_query
				)
			),
			$limit
		);

		$query_sanitized = preg_replace("/[^a-zA-Z0-9]+/", "", $query);
		$query_uri = urlencode($query);

		// let's check the API to see if we get any results
		if ($venues_api_result = $this->getCachedURL("CalendarPlant_findVenues",
			"venues_$query_sanitized", $this->venues_api."/venues/$query_uri")) {

			// we need to namespace the results from the API so we can switch accordingly
			$namespaced_results = array();

			foreach ($venues_api_result['results'] as $venue) {

				$venue['id'] = "venues.cashmusic.org:".$venue['UUID'];
				$namespaced_results[] = $venue;
			}

			// if $result is not falsy then we can just combine these two arrays
			if ($result) {

				if (count($namespaced_results) > 0) {
					$result = array_merge($result, $namespaced_results);
				}
			}

			// if $result is a no go then we can just replace it with our results from the API
			if (!$result) {
				$result = $namespaced_results;
			}
		}
//		error_log( print_r($result, true) );
		return $result;
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

	protected function editVenue($venue_id,$name=false,$address1=false,$address2=false,$city=false,$region=false,$country=false,$postalcode=false,$url=false,$phone=false) {
		$final_edits = array_filter(
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
			'CASHSystem::notExplicitFalse'
		);
		$result = $this->db->setData(
			'venues',
			$final_edits,
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

	protected function deleteEvent($event_id) {
		$result = $this->db->deleteData(
			'events',
			array(
				'id' => array(
					'condition' => '=',
					'value' => $event_id
				)
			)
		);
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

	protected function editEvent($event_id,$date=false,$venue_id=false,$purchase_url=false,$comment=false,$published=false,$cancelled=false,$user_id=false) {
		$condition = array(
			"id" => array(
				"condition" => "=",
				"value" => $event_id
			)
		);
		if ($user_id) {
			$condition['user_id'] = array(
				"condition" => "=",
				"value" => $user_id
			);
		}
		$final_edits = array_filter(
			array(
				'date' => $date,
				'venue_id' => $venue_id,
				'published' => $published,
				'cancelled' => $cancelled,
				'purchase_url' => $purchase_url,
				'comments' => $comment
			),
			'CASHSystem::notExplicitFalse'
		);
		$result = $this->db->setData(
			'events',
			$final_edits,
			$condition
		);
		return $result;
	}

	protected function getEvent($event_id) {

		$result = $this->db->getData(
			'events',
			'*',
			array(
				"id" => array(
					"condition" => "=",
					"value" => $event_id
				)
			)
		);

		$venue = $this->getVenue($result[0]['venue_id']);
		$results = array_merge($result[0], $venue);

		return $results;
	}

	protected function getEvents($user_id, $offset=0, $published_status=1, $cancelled_status=0, $cutoff_date_low=false, $cutoff_date_high=false, $visible_event_types="upcoming") {

		if (!$cutoff_date_low) {
			switch ($visible_event_types) {
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
		}

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

		if (!is_array($result)) {
			return false;
		}

		$events_with_venues = array();

		if (is_array($result)) {
			foreach ($result as $event) {
				// if we get a venue result, merge the arrays
				if ($venue = $this->getVenue($event['venue_id'])) {
					// remap to venue_
					$venue = array_combine(
						array_map(function($k){ return 'venue_'.$k; }, array_keys($venue)),
						$venue
					);

					$events_with_venues[] = array_merge($event, $venue);
				} else {
					$events_with_venues[] = $event;
				}

			}
		}


		return $events_with_venues;
	}

	protected function getVenue($venue_id) {
		$namespace = "venues.cashmusic.org";
		// check the id for venues.cashmusic.org namespacing, get from API if exists
		if (strpos($venue_id, $namespace) !== false) {
			$venue_id_array = explode(":", $venue_id);
			$venue_id_string = $venue_id_array[1];
			if ($venues_api_result = $this->getCachedURL("CalendarPlant_findVenues",
				"venues_$venue_id_string", $this->venues_api."/venue/$venue_id_string")) {

				$venue = $venues_api_result;

			}
		} else {
			// numeric id, so load the normal way
			$result = $this->db->getData(
				'venues',
				'*',
				array(
					"id" => array(
						"condition" => "=",
						"value" => (int) $venue_id
					)
				)
			);

			$venue = $result[0];
		}

		if ($venue) {
			return $venue;
		} else {
			return false;
		}
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
