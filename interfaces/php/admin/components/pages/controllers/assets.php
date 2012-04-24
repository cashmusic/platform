<?php
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

?>