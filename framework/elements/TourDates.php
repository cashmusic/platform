<?php
/**
 * Email For Download element
 *
 * @package tourdates.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2016, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 *
 * This file is generously sponsored by Peter Landry
 * Where is the Funk Machine?
 *
 **/
class TourDates extends ElementBase {
	public $type = 'tourdates';
	public $name = 'Tour Dates';

	public function getData() {
		$tourdates_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar',
				'cash_action' => 'getevents',
				'visible_event_types' => $this->options['visible_event_types'],
				'user_id' => $this->element['user_id']
			)
		);
		if ($tourdates_request->response['status_uid'] == "calendar_getevents_200") {
			// spit out the dates
			$max_dates = 60;
			if (isset($this->options['max_display_dates'])) {
				$max_dates = $this->options['max_display_dates'];
			}
			$all_events = $tourdates_request->response['payload'];
			if ($this->options['visible_event_types'] == 'archive' && is_array($all_events)) {
				$all_events = array_reverse($all_events);
			}
			$all_events = array_slice($all_events,0,$max_dates,true);
			foreach ($all_events as &$event) {
				if ($event['venue_city'] != ""){
					if (strtolower($event['venue_country']) == 'usa' || strtolower($event['venue_country']) == 'canada') {
						$event['location'] = $event['venue_city'] . ', ' . $event['venue_region'];
					} else {
						$event['location'] = $event['venue_city'] . ', ' . $event['venue_country'];
					}
				}

				$event['formatted_date'] = date('d F, Y',$event['date']);

				if (isset($this->options['date_format'])) {

				$date_format = $this->options['date_format'];
				// format dates
				if ($date_format == 'year-month-day'){ $event['formatted_date'] = date('Y F, D d',$event['date']);}
				else if ($date_format == 'month-day-year'){$event['formatted_date'] = date('F d, Y',$event['date']);}
				else if ($date_format == 'day-month-year'){$event['formatted_date'] = date('D d F, Y',$event['date']);}
				}

				if (!$event['venue_name']) $event['venue_name'] ='TBA';
			}
			// add all dates to the element data
			$this->element_data['all_events'] = $all_events;
			$this->setTemplate('tourdates');
		}
		return $this->element_data;
	}
} // END class
?>
