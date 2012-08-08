<?php
// Deal with download code requests
if (isset($_POST['add_codes_qty']) && $request_parameters[0]) {
	if ($_POST['add_codes_qty'] > 0) {
		$total_added = 0;
		for ($i = 1; $i <= $_POST['add_codes_qty']; $i++) {
			$addcode_request = new CASHRequest(
				array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'addlockcode',
					'asset_id' => $request_parameters[0]
				)
			);
			if ($addcode_request->response['payload']) {
				$total_added++;
			}
		}
		$cash_admin->page_data['page_message'] = 'Added ' . $total_added . 'new download codes';
	}
}

$asset_codes = false;
if ($request_parameters[0]) {
	$getcodes_request = new CASHRequest(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'getlockcodes',
			'scope_table_alias' => 'assets',
			'scope_table_id' => $request_parameters[0]
		)
	);
	$asset_codes = $getcodes_request->response['payload'];
}
if (isset($_POST['exportcodes']) && $request_parameters[0]) {
	header('Content-Disposition: attachment; filename="codes_' . $request_parameters[0] . '_export.csv"');
	if ($asset_codes) {
		echo '"code","creation date","claim date"' . "\n";
		foreach ($asset_codes as $code) {
		    echo '"' . $code['uid'] . '"';
			echo ',"' . date('M j, Y h:iA T',$code['creation_date']) . '"';
			if ($code['claim_date']) {
				echo ',"' . date('M j, Y h:iA T',$code['claim_date']) . '"';
			} else {
				echo ',"not claimed"';
			}
			echo "\n";
		}
	} else {
		$cash_admin->page_data['error_message'] = "Error getting codes.";
	}
	exit;
}

// parsing posted data:
if (isset($_POST['doassetedit'])) {
	$asset_parent = 0;
	$connection_id = 0;
	$asset_location = '';
	$asset_description = false;
	$metadata = false;
	if (isset($_POST['parent_id'])) $asset_parent = $_POST['parent_id'];
	if (isset($_POST['connection_id'])) $connection_id = $_POST['connection_id'];
	if (isset($_POST['asset_location'])) $asset_location = $_POST['asset_location'];
	if (isset($_POST['asset_description'])) $asset_location = $_POST['asset_description'];

	$metadata_and_tags = AdminHelper::parseMetaData($_POST);
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');

	if ($_POST['asset_type'] == 'release') {
		$metadata = array(
			'artist_name' => $_POST['artist_name'],
			'release_date' => $_POST['release_date'],
			'matrix_number' => $_POST['matrix_number'],
			'label_name' => $_POST['label_name'],
			'genre' => $_POST['genre'],
			'copyright' => $_POST['copyright'],
			'publishing' => $_POST['publishing']
		);
	}

	$edit_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'editasset',
			'id' => $request_parameters[0],
			'user_id' => $effective_user,
			'title' => $_POST['asset_title'],
			'description' => $asset_description,
			'location' => $asset_location,
			'connection_id' => $connection_id,
			'parent_id' => $asset_parent,
			'type' => $_POST['asset_type'],
			'tags' => $metadata_and_tags['tags_details'],
			'metadata' => $metadata
		),
		'asseteditattempt'
	);

	if (!$edit_response['payload']) {
		$cash_admin->page_data['error_message'] = "Error editing asset. Please try again";
	}
}

// if favorite status is being toggled:
if (isset($_REQUEST['togglefavorite']) && $request_parameters[0]) {
	if ($cash_admin->isAssetAFavorite($request_parameters[0])) {
		$cash_admin->unFavoriteAsset($request_parameters[0]);
	} else {
		$cash_admin->favoriteAsset($request_parameters[0]);
	}
}

// Get the current asset details:
$asset_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getasset',
		'id' => $request_parameters[0]
	),
	'getasset'
);
$cash_admin->page_data = array_merge($cash_admin->page_data,$asset_response['payload']);

// Metadata shizz:
if (isset($cash_admin->page_data['metadata'])) {
	if (is_array($cash_admin->page_data['metadata'])) {
		foreach ($cash_admin->page_data['metadata'] as $key => $value) {
			$cash_admin->page_data['metadata_' . $key] = $value;
		}
	}
}

// Deal with tags:
$tag_counter = 1;
$tag_markup = '';
if (is_array($asset_response['payload']['tags'])) {
	foreach ($asset_response['payload']['tags'] as $tag) {
		$tag_markup .= "<input type='text' name='tag$tag_counter' value='$tag' placeholder='Tag' />";
		$tag_counter = $tag_counter+1;
	}
}
$cash_admin->page_data['tag_counter'] = $tag_counter;
$cash_admin->page_data['tag_markup'] = $tag_markup;

// Reset page title to reflect the asset:
$cash_admin->page_data['ui_title'] = 'Edit “' . $cash_admin->page_data['title'] . '”';
// Set favorite status:
$cash_admin->page_data['is_favorite'] = $cash_admin->isAssetAFavorite($request_parameters[0]);
// Code count
if ($asset_codes) {
	$cash_admin->page_data['asset_codes_count'] = count($asset_codes);
}

if ($cash_admin->page_data['type'] == 'file') {
	// parent id options markup:
	$cash_admin->page_data['parent_options'] = '<option value="0" selected="selected">None</option>';
	$cash_admin->page_data['parent_options'] .= AdminHelper::echoFormOptions('assets',$cash_admin->page_data['parent_id'],$cash_admin->getAllFavoriteAssets(),true);
	// connection options markup:
	$cash_admin->page_data['connection_options'] = '<option value="0" selected="selected">None (Normal http:// link)</option>';
	$cash_admin->page_data['connection_options'] .= AdminHelper::echoConnectionsOptions('assets', $cash_admin->page_data['connection_id'], true);

	// set the view
	$cash_admin->setPageContentTemplate('assets_details_file');
} else if ($cash_admin->page_data['type'] == 'release') {
	// set the view
	$cash_admin->setPageContentTemplate('assets_details_release');
} else {
	// default back to the most basic view:
	$cash_admin->page_data['form_state_action'] = 'doassetedit';
	$cash_admin->page_data['asset_button_text'] = 'Edit the asset';
	// create type options with current selected:
	$type_options = array(
		'file' => 'File',
		'folder' => 'Folder (depreciated. aka: going away before v4)',
		'playlist' => 'Playlist',
		'release' => 'Release'
	);
	$cash_admin->page_data['type_options_markup'] = '';
	foreach ($type_options as $type => $value) {
		if ($cash_admin->page_data['type'] == $type) {
			$selected = ' selected="selected"';
		} else {
			$selected = '';
		}
		$cash_admin->page_data['type_options_markup'] .= '<option value="' . $type . '"' . $selected . '>' . $value . '</option>';
	}
	$cash_admin->setPageContentTemplate('assets_details');
}
?>