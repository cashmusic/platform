<?php
/**
 * Email For Download element
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
class TourDates extends ElementBase {
	const type = 'tourdates';
	const name = 'Tour Dates';

	public function getMarkup() {
		$markup = '';
		$tourdates_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'getevents',
				'visible_event_types' => $this->options->visible_event_types,
				'user_id' => (integer) $this->element['user_id']
			)
		);
		if ($tourdates_request->response['status_uid'] == "calendar_getevents_200") {
			// spit out the dates
			foreach ($tourdates_request->response['payload'] as $event) {
				$event_location = $event['venue_city'] . ', ' . $event['venue_country'];
				if (strtolower($event['venue_country']) == 'usa' || strtolower($event['venue_country']) == 'canada') {
					$event_location = $event['venue_city'] . ', ' . $event['venue_region'];
				}
				$markup .= '<div class="cash_'. self::type .'_event"> '
						. '<div class="cash_'. self::type .'_timeandplace"> '
						. '<span class="cash_'. self::type .'_date">' . date('d F, Y',$event['date']) . ':</span> ';
				if ($event['venue_name']) {
					$markup .= '<span class="cash_'. self::type .'_location">' . $event_location . '</span> '
							. '<span class="cash_'. self::type .'_venue">@ ' . $event['venue_name'] . '</span> ';
				} else {
					$markup .= '<span class="cash_'. self::type .'_location">TBA</span> ';
				}	
				$markup .= '</div> ';
				if ($event['comments']) {
					$markup .= '<span class="cash_'. self::type .'_comments">' . $event['comments'] . '</span> ';
				}
				if ($event['purchase_url']) {
					$markup .= '<span class="cash_'. self::type .'_purchase_url"><a href="' . $event['purchase_url'] . '" class="external">Tickets</a></span> ';
				}
				if ($event['venue_address1'] && $event['venue_city'] && $event['venue_country']) {
					$markup .= '<span class="cash_'. self::type .'_purchase_url"><a href="http://maps.google.com/maps?f=q&hl=en&geocode=&q=' . $event['venue_address1'] . '+' . $event['venue_city'] . '+' . $event['venue_region'] . '+' . $event['venue_country'] . '+(' . $event['venue_name'] . ')" class="external">Map</a></span> ';
				}
				$markup .= '</div>';
			}
		} else {
			// no dates matched
			$markup .= 'There are no dates to display right now.';
		}
		return $markup;
	}
} // END class 
?>