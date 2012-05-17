<?php
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

$lists_response = new CASHRequest(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlistsforuser',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	)
);
$lists_array = array();
$lists_count = 1;
foreach ($lists_response->response['payload'] as $list) {
	$analytics_request = new CASHRequest(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'getanalytics',
			'analtyics_type' => 'listmembership',
			'list_id' => $list['id'],
			'user_id' => AdminHelper::getPersistentData('cash_effective_user')
		)
	);
	$lists_array[] = array(
		'id' => $list['id'],
		'name' => $list['name'],
		'total' => $analytics_request->response['payload']['active'],
		'lastweek' => $analytics_request->response['payload']['last_week']
	);
	unset($analytics_request);
	$lists_count++;
	if ($lists_count > 3) {
		break;
	}
}
?>