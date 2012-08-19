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

$allfuture_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'getevents',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
		'visible_event_types' => 'upcoming'
	)
);

if (is_array($allfuture_response['payload'])) {
	formatEventOutput($allfuture_response);
	$cash_admin->page_data['events_allfuture'] = new ArrayIterator($allfuture_response['payload']);
}

$cash_admin->page_data['options_venues'] = AdminHelper::echoFormOptions('venues',0,false,true);

$cash_admin->setPageContentTemplate('calendar_events');
?>