<?php
// banner stuff
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	$cash_admin->page_data['banner_title_content'] = '<b>upload</b> files<br /><b>organize</b> assets for use<br />add <b>tags</b> and <b>metadata</b>';
	$cash_admin->page_data['banner_main_content'] = 'Enter details about all the files that matter to you, either on a connected S3 account or simple URLs. These assets will be used in the elements you define.';
}

$user_id = $cash_admin->effective_user_id;
// get all assets for page
$releases_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getassetsforuser',
		'type' => 'release',
		'parent_id' => 0,
		'user_id' => $user_id
	)
);
$playlists_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getassetsforuser',
		'type' => 'playlist',
		'parent_id' => 0,
		'user_id' => $user_id
	)
);
$files_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getassetsforuser',
		'type' => 'file',
		'parent_id' => 0,
		'user_id' => $user_id
	)
);

if (is_array($releases_response['payload'])) {
	$releases_response['payload'] = array_reverse($releases_response['payload']); // newest first
	$asset_count = 0;
	foreach ($releases_response['payload'] as &$asset) {
		$asset_count++;
		if ($asset_count % 3 == 0) {
			$asset['third_asset'] = true;
		}
		if ($asset_count == 3) {
			$asset['last_feature'] = true;	
		}
		$asset['descriptor_string'] = 'created: ' . CASHSystem::formatTimeAgo($asset['creation_date']);
		if ($asset['modification_date']) {
			$asset['descriptor_string'] .= '<br />last edited: ' . CASHSystem::formatTimeAgo($asset['modification_date']);
		}


		$asset['cover_url'] = ADMIN_WWW_BASE_PATH . '/assets/images/release.jpg';
		if (isset($asset['metadata']['cover'])) {
			if ($asset['metadata']['cover']) { // effectively non-zero
				$cover_response = $cash_admin->requestAndStore(
					array(
						'cash_request_type' => 'asset', 
						'cash_action' => 'getasset',
						'id' => $asset['metadata']['cover']
					)
				);
				if ($cover_response['payload']) {
					$cover_asset = $cover_response['payload'];
					if (strpos(CASHSystem::getMimeTypeFor($cover_asset['location']),'image') !== false) {
						$cover_url_response = $cash_admin->requestAndStore(
							array(
								'cash_request_type' => 'asset', 
								'cash_action' => 'getasseturl',
								'connection_id' => $cover_asset['connection_id'],
								'user_id' => AdminHelper::getPersistentData('cash_effective_user'),
								'asset_location' => $cover_asset['location'],
								'inline' => true
							)
						);
						if ($cover_url_response['payload']) {
							$asset['cover_url'] = $cover_url_response['payload'];
						}
					}
				}
			}
		}



	}
	$featured_releases = array_slice($releases_response['payload'],0,3);
	$cash_admin->page_data['featured_releases'] = new ArrayIterator($featured_releases);
	if (count($releases_response['payload']) > 3) {
		$remaining_releases = array_slice($releases_response['payload'],3);
		$cash_admin->page_data['more_releases'] = true;
		$cash_admin->page_data['remaining_releases'] = new ArrayIterator($remaining_releases);
	}
}
if (is_array($playlists_response['payload'])) {
	$playlists_response['payload'] = array_reverse($playlists_response['payload']); // newest first
	$asset_count = 0;
	foreach ($playlists_response['payload'] as &$asset) {
		$asset_count++;
		if ($asset_count % 3 == 0) {
			$asset['third_asset'] = true;
		}
		if ($asset_count == 6) {
			$asset['last_feature'] = true;	
		}
		$asset['descriptor_string'] = 'created: ' . CASHSystem::formatTimeAgo($asset['creation_date']);
		if ($asset['modification_date']) {
			$asset['descriptor_string'] = 'last edited: ' . CASHSystem::formatTimeAgo($asset['modification_date']);
		}
	}
	$featured_playlists = array_slice($playlists_response['payload'],0,6);
	$cash_admin->page_data['featured_playlists'] = new ArrayIterator($featured_playlists);
	if (count($playlists_response['payload']) > 6) {
		$remaining_playlists = array_slice($playlists_response['payload'],6);
		$cash_admin->page_data['more_playlists'] = true;
		$cash_admin->page_data['remaining_playlists'] = new ArrayIterator($remaining_playlists);
	}
}
if (is_array($files_response['payload'])) {
	$files_response['payload'] = array_reverse($files_response['payload']); // newest first
	foreach ($files_response['payload'] as &$asset) {
		$asset['descriptor_string'] = 'created: ' . CASHSystem::formatTimeAgo($asset['creation_date']);
		if ($asset['modification_date']) {
			$asset['descriptor_string'] .= ' / last edited: ' . CASHSystem::formatTimeAgo($asset['modification_date']);
		}
	}
	$featured_files = array_slice($files_response['payload'],0,10);
	if (count($files_response['payload']) > 10) {
		$remaining_files = array_slice($files_response['payload'],10);
		$cash_admin->page_data['more_files'] = true;
		$cash_admin->page_data['remaining_files'] = new ArrayIterator($remaining_files);
	}
	$cash_admin->page_data['featured_files'] = new ArrayIterator($featured_files);
}


$cash_admin->setPageContentTemplate('assets');
?>