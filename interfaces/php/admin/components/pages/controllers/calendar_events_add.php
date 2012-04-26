<?php
// parsing posted data:
if (isset($_POST['doeventadd'])) {
	// do the actual list add stuffs...
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	$eventispublished = 0;
	$eventiscancelled = 0;
	if (isset($_POST['event_ispublished'])) { $eventispublished = 1; }
	if (isset($_POST['event_iscancelled'])) { $eventiscancelled = 1; }
	$cash_admin->requestAndStore(
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
		),
		'eventaddattempt'
	);
}
?>