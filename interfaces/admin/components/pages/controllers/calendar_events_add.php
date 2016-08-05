<?php
// parsing posted data:
if (isset($_POST['doeventadd'])) {
	// do the actual list add stuffs...
	$effective_user = $cash_admin->effective_user_id;
	$eventispublished = 0;
	$eventiscancelled = 0;
	if (isset($_POST['event_ispublished'])) { $eventispublished = 1; }
	if (isset($_POST['event_iscancelled'])) { $eventiscancelled = 1; }
	$add_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'calendar',
			'cash_action' => 'addevent',
			'date' => strtotime($_POST['event_date']),
			'venue_id' => $_POST['event_venue'],
			'comment' => $_POST['event_comment'],
			'purchase_url' => $_POST['event_purchase_url'],
			'published' => $eventispublished,
			'cancelled' => $eventiscancelled,
			'user_id' => $effective_user,
		)
	);

	if ($add_response['payload']) {
		AdminHelper::formSuccess('Success. Event added.','/calendar/events/');
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.','/calendar/events/add/');
	}
}

$cash_admin->page_data['venue_options'] = AdminHelper::echoFormOptions('venues',0,false,true);
$cash_admin->page_data['form_state_action'] = 'doeventadd';
$cash_admin->page_data['event_button_text'] = 'Add the event';

$cash_admin->setPageContentTemplate('calendar_events_details');
?>
