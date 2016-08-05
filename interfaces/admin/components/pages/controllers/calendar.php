<?php
function formatEventOutput(&$response) {
	foreach ($response['payload'] as &$event) {
		// fix empty venue name
		if (!$event['venue_name']) {
			$event['venue_name'] = 'TBA';
		}
		// format date for viewing
		$event['formatted_date'] = date('d M',$event['date']);
		// format location
		if (strtolower($event['venue_country']) == 'usa' || strtolower($event['venue_country']) == 'canada') {
			$event['event_location'] = $event['venue_city'] . ', ' . $event['venue_region'];
		} else {
			$event['event_location'] = $event['venue_city'] . ', ' . $event['venue_country'];
		}
		if ($event['event_location'] == ', ') {
			$event['event_location'] = '';
		}
	}
}

$thisweek_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar',
		'cash_action' => 'getevents',
		'user_id' => $cash_admin->effective_user_id,
		'cutoff_date_low' => 'now',
		'cutoff_date_high' => time() + (60*60*24*7) // weird time format, but easy to understand
	)
);
$unpublished_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar',
		'cash_action' => 'getevents',
		'user_id' => $cash_admin->effective_user_id,
		'visible_event_types' => 'upcoming',
		'published_status' => 0
	)
);

// this week
if (is_array($thisweek_response['payload'])) {
	formatEventOutput($thisweek_response);
	$cash_admin->page_data['events_thisweek'] = new ArrayIterator($thisweek_response['payload']);
}

// unpublished
// most accessed
if (is_array($unpublished_response['payload'])) {
	formatEventOutput($unpublished_response);
	$cash_admin->page_data['events_unpublished'] = new ArrayIterator($unpublished_response['payload']);
}

// Any events at all? (first use)
$allevents_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar',
		'cash_action' => 'getevents',
		'user_id' => $cash_admin->effective_user_id,
		'visible_event_types' => 'both'
	)
);

$cash_admin->page_data['no_events'] = !$allevents_response['payload'];


$cash_admin->setPageContentTemplate('calendar');
?>
