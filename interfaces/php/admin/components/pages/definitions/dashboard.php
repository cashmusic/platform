<?php
// add unique page settings:
$page_title = 'Main Page';
$page_tips = 'Here\'s an overview of your account. Look for help tips on every page.';
$page_data = array();

// most accessed assets
$eval_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'mostaccessed',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'asset_mostaccessed'
);

// recently added assets
$eval_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'recentlyadded',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'asset_recentlyadded'
);

// next week of events
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

// most active elements
$eval_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'mostactive',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'element_mostactive'
);

// recently added elements
$eval_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'recentlyadded',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'element_recentlyadded'
);

// get all elements
$eval_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getelementsforuser',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'element_allelements'
);

// count the active elements
if ($cash_admin->getStoredResponse('element_mostactive',true)) {
	$cash_admin->storeData(count($cash_admin->getStoredResponse('element_mostactive',true)),'element_active_count');
} 
// if active elements are found, subtract them from the total to get inactive elements
if ($cash_admin->getStoredData('element_active_count')) {
	$cash_admin->storeData(count($cash_admin->getStoredResponse('element_allelements',true)) - $cash_admin->getStoredData('element_active_count'),'element_inactive_count');
} 
?>