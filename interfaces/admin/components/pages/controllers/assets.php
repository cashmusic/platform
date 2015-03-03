<?php

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
/*
$playlists_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getassetsforuser',
		'type' => 'playlist',
		'parent_id' => 0,
		'user_id' => $user_id
	)
);
*/
$files_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getassetsforuser',
		'type' => 'file',
		'parent_id' => 0,
		'user_id' => $user_id
	)
);

// we need to get all items for the user to determine if an asset is monetized
$items_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getitemsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

//Commerce connection, release or files present?
$cash_admin->page_data['connection'] = AdminHelper::getConnectionsByScope('assets') || $releases_response['payload'] || $files_response['payload']; 

// Return Connection
$page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
$settings_types_data = $page_data_object->getConnectionTypes('assets');

$all_services = array();
$typecount = 1;
foreach ($settings_types_data as $key => $data) {
	if ($typecount % 2 == 0) {
		$alternating_type = true;
	} else {
		$alternating_type = false;
	}
	if (file_exists(ADMIN_BASE_PATH.'/assets/images/settings/' . $key . '.png')) {
		$service_has_image = true;
	} else {
		$service_has_image = false;
	}
	if (in_array($cash_admin->platform_type, $data['compatibility'])) {
		$all_services[] = array(
			'key' => $key,
			'name' => $data['name'],
			'description' => $data['description'],
			'link' => $data['link'],
			'alternating_type' => $alternating_type,
			'service_has_image' => $service_has_image
		);
		$typecount++;
	}
}
$cash_admin->page_data['all_services'] = new ArrayIterator($all_services);


// releases

if (is_array($releases_response['payload'])) {
	$releases_response['payload'] = array_reverse($releases_response['payload']); // newest first
	if (count($releases_response['payload']) == 2) {
		$cash_admin->page_data['one_remaining'] = true;	
	} else if (count($releases_response['payload']) == 2) {
		$cash_admin->page_data['two_remaining'] = true;
	}
	foreach ($releases_response['payload'] as &$asset) {
		if ($asset['modification_date']) {
			$asset['descriptor_string'] = 'updated: ' . CASHSystem::formatTimeAgo($asset['modification_date']);
		} else {
			$asset['descriptor_string'] = 'updated: ' . CASHSystem::formatTimeAgo($asset['creation_date']);	
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

		if (isset($asset['metadata']['artist_name'])) {
			$asset['artist_name'] = $asset['metadata']['artist_name'];
		}

		if (isset($asset['metadata']['fulfillment'])) {
			if (is_array($asset['metadata']['fulfillment'])) {
				if (count($asset['metadata']['fulfillment'])) {
					$asset['has_fulfillment'] = true;
				}
			}
		}

		if (is_array($items_response['payload'])) {
			foreach ($items_response['payload'] as $item) {
				if ($item['fulfillment_asset'] == $asset['id']) {
					$asset['monetized'] = true;
					break;
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
/*
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
*/
if (is_array($files_response['payload'])) {
	$files_response['payload'] = array_reverse($files_response['payload']); // newest first
	foreach ($files_response['payload'] as &$asset) {
		if ($asset['modification_date']) {
			$asset['descriptor_string'] = 'updated: ' . CASHSystem::formatTimeAgo($asset['modification_date']);
		} else {
			$asset['descriptor_string'] = 'updated: ' . CASHSystem::formatTimeAgo($asset['creation_date']);	
		}

		if (is_array($items_response['payload'])) {
			foreach ($items_response['payload'] as $item) {
				if ($item['fulfillment_asset'] == $asset['id']) {
					$asset['monetized'] = true;
					break;
				}
			}
		}
	}

	$featured_files = array_slice($files_response['payload'],0,5);
	if (count($files_response['payload']) > 5) {
		$remaining_files = array_slice($files_response['payload'],5);
		$cash_admin->page_data['more_files'] = true;
		$cash_admin->page_data['remaining_files'] = new ArrayIterator($remaining_files);
	}
	$cash_admin->page_data['featured_files'] = new ArrayIterator($featured_files);
}


$cash_admin->setPageContentTemplate('assets');
?>