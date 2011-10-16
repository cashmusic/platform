<?php
// add unique page settings:
$page_title = 'Calendar: Events';
$page_tips = "You can have as many unpublished dates in the system as you like, publishing them when it's time to announce a tour.<br /><br />The cancellation field exists so you can publicly mark an already publicized show as cancelled,	updating the comments with relevant information about refunds, etc.";

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
		$markup .= '<ul class="alternating"> ';
		$loopcount = 1;
		foreach ($dates_response['payload'] as $event) {
			$event_location = $event['venue_city'] . ', ' . $event['venue_country'];
			if (strtolower($event['venue_country']) == 'usa' || strtolower($event['venue_country']) == 'canada') {
				$event_location = $event['venue_city'] . ', ' . $event['venue_region'];
			}
			$altclass = '';
			if ($loopcount % 2 == 0) { $altclass = ' class="alternate"'; }
			$markup .= '<li' . $altclass . '> '
					. '<b>' . date('d M',$event['date']) . ': ' . $event_location . '</b> '
					. '<span class="nobr">@ ' . $event['venue_name'] . '</span>';

			$markup .= '<br /><a href="' . $event['event_id'] . '" class="spaced noblock">Edit</a> <a href="../delete/' . $event['event_id'] . '" class="needsconfirmation noblock">Delete</a>';
			$markup .= '</li>';
			$loopcount = $loopcount + 1;
		}
		$markup .= '</ul>';
	} else {
		// no dates matched
		$markup .= 'There are no upcoming dates.';
	}
	return $markup;
}
?>