<?php
// add unique page settings:
$page_title = 'Calendar: Main';
$page_tips = 'Here folks will manage their show calendars, guestlists, and choose what is published in any elements they create.';
$page_memu = array(
	'Calendar' => array(
		'calendar/events/' => 'Events',
		'calendar/venues/' => 'Venues',
		'calendar/guestlists/' => 'Guestlists'
	)
);

$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'geteventsbetween',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
		'cutoff_date_low' => 'now',
		'cutoff_date_high' => time() + (60*60*24*7) // weird time format, but easy to understand
	),
	'events_thisweek'
);
$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'calendar', 
		'cash_action' => 'getevents',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
		'visible_event_types' => 'upcoming',
		'published_status' => 0
	),
	'events_unpublished'
);
?>