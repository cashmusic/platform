<?php
// add unique page settings:
$page_title = 'Calendar: Events';
$page_tips = '';

$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'gettourdates',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
		'visible_event_types' => 'upcoming'
	),
	'events_allfuture'
);

function calendar_events_format_dates($dates_response) {
	$markup = '';
	if ($dates_response['status_uid'] == "calendar_gettourdates_200" || $dates_response['status_uid'] == "calendar_gettourdatesbetween_200") {
		// spit out the dates
		$markup .= '<ul class="nobullets"> ';
		foreach ($dates_response['payload'] as $event) {
			$event_location = $event['venue_city'] . ', ' . $event['venue_country'];
			if (strtolower($event['venue_country']) == 'usa' || strtolower($event['venue_country']) == 'canada') {
				$event_location = $event['venue_city'] . ', ' . $event['venue_region'];
			}
			$markup .= '<li> '
					. '<b>' . date('d M',$event['date']) . ': ' . $event_location . '</b> '
					. '@ ' . $event['venue_name'];

			$markup .= '<br /><a href="' . $event['event_id'] . '" class="spaced">Edit</a> <a href="../delete/' . $event['event_id'] . '" class="needsconfirmation">Delete</a>';
			$markup .= '</li>';
		}
		$markup .= '</ul>';
	} else {
		// no dates matched
		$markup .= 'There are no upcoming dates.';
	}
	return $markup;
}
?>