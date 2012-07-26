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
} else {
	$add_type = 'file';
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
$cash_admin->setPageContentTemplate('assets_details');
?>