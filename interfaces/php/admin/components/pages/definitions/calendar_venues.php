<?php
// add unique page settings:
$page_title = 'Calendar: Venues';
$page_tips = '';

$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'getallvenues',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
		'visible_event_types' => 'upcoming'
	),
	'getallvenues'
);

$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'system', 
		'cash_action' => 'setapicredentials',
		'user_id' => 1
	),
	'apishit'
);
?>