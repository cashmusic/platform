<?php
// parsing posted data:
if (isset($_POST['doeventedit'])) {
	// do the actual list add stuffs...
	$event_id = $request_parameters[0];
	$eventispublished = 0;
	$eventiscancelled = 0;
	if (isset($_POST['event_ispublished'])) { $eventispublished = 1; }
	if (isset($_POST['event_iscancelled'])) { $eventiscancelled = 1; }
	$edit_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'calendar',
			'cash_action' => 'editevent',
			'date' => strtotime($_POST['event_date']),
			'venue_id' => $_POST['event_venue'],
			'comment' => $_POST['event_comment'],
			'purchase_url' => $_POST['event_purchase_url'],
			'published' => $eventispublished,
			'cancelled' => $eventiscancelled,
			'event_id' => $event_id,
		)
	);
	if ($edit_response['status_uid'] == 'calendar_editevent_200') {
		AdminHelper::formSuccess('Success. Edited.','/calendar/events/');
	} else {
		AdminHelper::formFailure('Error. There was a problem editing.');
	}
}

$event_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar',
		'cash_action' => 'getevent',
		'event_id' => $request_parameters[0]
	)
);

$current_event = $event_response['payload'];

if (is_array($current_event)) {
	$cash_admin->page_data = array_merge($cash_admin->page_data,$current_event);

	$venue_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'calendar',
			'cash_action' => 'getvenue',
			'venue_id' => $current_event['venue_id']
		)
	);

	$venue_details = $venue_response['payload'];
	if ($venue_details) {
		$display_string = $venue_details['name'];
		if (strtolower($venue_details['country']) == 'usa' || strtolower($venue_details['country']) == 'canada') {
			$display_string .= ' / ' . $venue_details['city'] . ', ' . $venue_details['region'];
		} else {
			$display_string .= ' / ' . $venue_details['city'] . ', ' . $venue_details['country'];
		}
		$cash_admin->page_data['venue_display_string'] = $display_string;
	} else {
		$cash_admin->page_data['venue_display_string'] = 'TBA';
	}
}

$cash_admin->page_data['formatted_date'] = date('m/j/Y h:iA T',$current_event['date']);
if ($cash_admin->page_data['published']) {
	$cash_admin->page_data['published'] = 1;
}
$cash_admin->page_data['form_state_action'] = 'doeventedit';
$cash_admin->page_data['event_button_text'] = 'Save changes';

$cash_admin->setPageContentTemplate('calendar_events_details');
?>
