<?php
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
		echo "Error getting codes.";
	}
	exit;
}



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
	$asset_settings = $_POST['connection_id'];
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
			'connection_id' => $asset_settings,
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

if (isset($_REQUEST['togglefavorite']) && $request_parameters[0]) {
	if ($cash_admin->isAssetAFavorite($request_parameters[0])) {
		$cash_admin->unFavoriteAsset($request_parameters[0]);
	} else {
		$cash_admin->favoriteAsset($request_parameters[0]);
	}
}

$page_message = false;
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
		$page_message = 'Added ' . $total_added . 'new download codes';
	}
}

$cash_admin->storeData($cash_admin->isAssetAFavorite($request_parameters[0]),'is_favorite');
?>