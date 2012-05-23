<?php
$mostactive_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'mostactive',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'mostactive'
);

$recentlyadded_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'recentlyadded',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'recentlyadded'
);

// banner stuff
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	$cash_admin->page_data['banner_title_content'] = '<h2>Elements</h2>manage your <b>contacts</b><br />create and maintain <b>lists</b><br />monitor <b>social</b> media';
	$cash_admin->page_data['banner_main_content'] = 'Combine everything else and build functionality, check analytics for existing elements, and get embed codes to use your elements on your site.';
}

// most active
if (is_array($mostactive_response['payload'])) {
	$cash_admin->page_data['elements_mostactive'] = new ArrayIterator($mostactive_response['payload']);
}

// recently added
if (is_array($recentlyadded_response['payload'])) {
	$cash_admin->page_data['elements_recentlyadded'] = new ArrayIterator(array_slice($recentlyadded_response['payload'],6));
}

$cash_admin->setPageContentTemplate('elements');
?>