<?php
// add unique page settings:
$page_title = 'Calendar: Main';
$page_tips = 'Here folks will manage their show calendars, guestlists, and choose what is published in any elements they create.';
$page_memu = array(
	'Actions' => array(
		'calendar/events/' => 'Events',
		'calendar/venues/' => 'Venues',
		'calendar/guestlists/' => 'Guestlists'
	)
);

$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'gettourdatesbetween',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
		'cutoff_date_low' => 'now',
		'cutoff_date_high' => time() + (60*60*24*7) // weird time format, but easy to understand
	),
	'events_thisweek'
);
$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'gettourdates',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
		'cash_action' => 'gettourdates',
		'published_status' => 0
	),
	'events_unpublished'
);

function calendar_format_dates($dates_response) {
	$markup = '';
	if ($dates_response['status_uid'] == "calendar_gettourdates_200" || $dates_response['status_uid'] == "calendar_gettourdatesbetween_200") {
		// spit out the dates
		foreach ($dates_response['payload'] as $event) {
			$event_location = $event['venue_city'] . ', ' . $event['venue_country'];
			if (strtolower($event['venue_country']) == 'usa' || strtolower($event['venue_country']) == 'canada') {
				$event_location = $event['venue_city'] . ', ' . $event['venue_region'];
			}
			$markup .= '<div class="callout"> '
					. '<h4>' . date('d F',$event['date']) . ': ' . $event_location . '</h4> '
					. '<b>@ ' . $event['venue_name'] . '</b> - ';
			if ($event['comments']) {
				$markup .= $event['comments'];
			}
			$markup .= '<br /><span class="smalltext fadedtext nobr">Created: ' . date('M jS, Y',$event['creation_date']); 
			if ($event['modification_date']) { 
				$markup .= ' (Modified: ' . date('F jS, Y',$event['modification_date']) . ')'; 
			}
			$markup .= '</span>';
			
			$markup .= '<div class="tar"><br /><a href="' . $event['event_id'] . '" class="mininav">Edit</a> <a href="../delete/' . $event['event_id'] . '" class="needsconfirmation mininav">Delete</a></div>';
			$markup .= '</div>';
		}
	} else {
		// no dates matched
		$markup .= 'There are no dates of this type to display right now.';
	}
	return $markup;
}
?>