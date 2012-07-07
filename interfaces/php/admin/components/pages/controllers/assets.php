<?php
// most accessed assets
$mostaccessed_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'mostaccessed',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'asset_mostaccessed'
);

// banner stuff
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	$cash_admin->page_data['banner_title_content'] = '<b>upload</b> files<br /><b>organize</b> assets for use<br />add <b>tags</b> and <b>metadata</b>';
	$cash_admin->page_data['banner_main_content'] = 'Enter details about all the files that matter to you, either on a connected S3 account or simple URLs. These assets will be used in the elements you define.';
}

// most accessed
if (is_array($mostaccessed_response['payload'])) {
	$cash_admin->page_data['mostaccessed_assets'] = new ArrayIterator(array_slice($mostaccessed_response['payload'],0,5));
}

$cash_admin->setPageContentTemplate('assets');
?>