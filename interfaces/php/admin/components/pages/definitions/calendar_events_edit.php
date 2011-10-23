<?php
// add unique page settings:
$page_title = 'Calendar: Edit Event';
$page_tips = "You can have as many unpublished dates in the system as you like, publishing them when it's time to announce a tour.<br /><br />The cancellation field exists so you can publicly mark an already publicized show as cancelled,	updating the comments with relevant information about refunds, etc.";

$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'getevent',
		'event_id' => $request_parameters[0]
	),
	'getevent'
);

// parsing posted data:
if (isset($_POST['doeventedit'])) {
	// do the actual list add stuffs...
	$event_id = $request_parameters[0];
	$eventispublished = 0;
	$eventiscancelled = 0;
	if (isset($_POST['event_ispublished'])) { $eventispublished = 1; }
	if (isset($_POST['event_iscancelled'])) { $eventiscancelled = 1; }
	$cash_admin->requestAndStore(
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
		),
		'eventeditattempt'
	);
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'calendar', 
			'cash_action' => 'getevent',
			'event_id' => $request_parameters[0]
		),
		'getevent'
	);
}
?>