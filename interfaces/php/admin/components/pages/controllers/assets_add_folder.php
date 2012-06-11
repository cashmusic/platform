<?php
// parsing posted data:
if (isset($_POST['doassetadd'])) {
	$asset_title = $_POST['asset_title'];
	$asset_description = $_POST['asset_description'];
	
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'addasset',
			'title' => $asset_title,
			'description' => $asset_description,
			'location' => '',
			'type' => 'folder',
			'user_id' => $effective_user,
		),
		'addasset'
	);
}
?>