<?php
// add unique page settings:
$page_title = 'Assets: Edit Asset';
$page_tips = 'Edit a single file.';

$cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getasset',
		'id' => $request_parameters[0]
	),
	'getasset'
);

// parsing posted data:
if (isset($_POST['doassetedit'])) {
	$asset_settings = $_POST['settings_id'];
	$asset_title = $_POST['asset_title'];
	$asset_location = $_POST['asset_location'];
	$asset_description = $_POST['asset_description'];

	$metadata_and_tags = AdminHelper::parseMetaData($_POST);
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'editasset',
			'id' => $request_parameters[0],
			'user_id' => $effective_user,
			'title' => $asset_title,
			'description' => $asset_description,
			'location' => $asset_location,
			'user_id' => $effective_user,
			'settings_id' => $asset_settings,
			'tags' => $metadata_and_tags['tags_details'],
			'metadata' => $metadata_and_tags['metadata_details']
		),
		'asseteditattempt'
	);
	
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'getasset',
			'id' => $request_parameters[0]
		),
		'getasset'
	);
}
?>