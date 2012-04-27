<?php
// add unique page settings:
$page_title = 'Calendar: Events';
$page_tips = "You can have as many unpublished dates in the system as you like, publishing them when it's time to announce a tour.<br /><br />The cancellation field exists so you can publicly mark an already publicized show as cancelled,	updating the comments with relevant information about refunds, etc.";

$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'getevents',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
		'visible_event_types' => 'upcoming'
	),
	'events_allfuture'
);
?>