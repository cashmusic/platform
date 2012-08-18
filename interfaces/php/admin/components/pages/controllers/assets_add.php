<?php
// parsing posted data:
if (isset($_POST['doassetadd'])) {
	
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	$add_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'addasset',
			'title' => $_POST['asset_title'],
			'description' => $_POST['asset_description'],
			'parent_id' => $_POST['parent_id'],
			'connection_id' => $_POST['connection_id'],
			'location' => $_POST['asset_location'],
			'user_id' => $effective_user,
			'type' => $_POST['asset_type']
		),
		'addasset'
	);

	if ($add_response['payload']) {
		AdminHelper::formSuccess('Success. Here\'s your new asset. Feel free to start adding details.','/assets/edit/' . $add_response['payload']);
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.','/assets/add/');
	}
}

$cash_admin->page_data['form_state_action'] = 'doassetadd';
$cash_admin->page_data['asset_button_text'] = 'Add that asset';
// create type options with current selected:
if (isset($request_parameters[0])) {
	$add_type = $request_parameters[0];
	$cash_admin->page_data['force_type'] = $add_type;
	$cash_admin->page_data['ui_title'] = 'Assets: Add a ' . $add_type;
	if ($add_type == 'file') {
		// connection options markup:
		$cash_admin->page_data['connection_options'] = '<option value="0" selected="selected">None (Normal http:// link)</option>';
		$cash_admin->page_data['connection_options'] .= AdminHelper::echoConnectionsOptions('assets', 0, true);

		$cash_admin->page_data['show_location'] = true;
	}
} else {
	$add_type = 'file';
	$cash_admin->page_data['ui_title'] = 'Assets: Add an asset';
}
$type_options = array(
	'file' => 'File',
	'folder' => 'Folder (depreciated. aka: going away before v4)',
	'playlist' => 'Playlist',
	'release' => 'Release'
);
$cash_admin->page_data['type_options_markup'] = '';
foreach ($type_options as $type => $value) {
	if ($add_type == $type) {
		$selected = ' selected="selected"';
	} else {
		$selected = '';
	}
	$cash_admin->page_data['type_options_markup'] .= '<option value="' . $type . '"' . $selected . '>' . $value . '</option>';
}
// check the third parameter is set it's the parent id (/assets/add/file/setparent/3)
if (isset($request_parameters[2])) {
	$cash_admin->page_data['parent_id'] = $request_parameters[2];
} else {
	$cash_admin->page_data['parent_id'] = 0;
}

$cash_admin->page_data['assets_add_action'] = true;
$cash_admin->setPageContentTemplate('assets_details');
?>