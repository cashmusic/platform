<?php
$page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
$applicable_connections = $page_data_object->getConnectionsByScope('assets');

$connection_id = 0;
$browse_path = false;
$browse_depth = 0;

// check $request_parameters for connection and path
if (is_array($request_parameters)) {
	if (array_shift($request_parameters) == 'connection') {
		$connection_id = array_shift($request_parameters);
		$browse_path = implode('/',$request_parameters);
		// no path found, set it to '.' for root
		if ($browse_path == '') {
			$browse_path = '.';
		} else {
			// path found. compute depth from root by counting slashes
			$browse_depth = count(explode('/',$browse_path));
		}
	}
}

// look for local-only assets
$local_assets_reponse =  $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'asset', 
		'cash_action' => 'getassetsforconnection',
		'connection_id' => 0
	),
	'localassets'
);
$local_assets = false;
if (is_array($local_assets_reponse['payload'])) {
	$filecount = count($local_assets_reponse['payload']);
	if ($filecount) {
		$local_assets = true;
	}
}

$list_connections = false;
$list_assets = false;
if (is_array($applicable_connections)) {
	if (!$browse_path) {
		// no browse path at all means we're on the main browse page
		// list connections and categories instead of assets
		$list_connections = array();
		foreach ($applicable_connections as $connection) {
			$assets_reponse = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'getassetsforconnection',
					'connection_id' => $connection['id']
				),
				'allassets'
			);
			if (is_array($assets_reponse['payload'])) {
				$filecount = count($assets_reponse['payload']);
				$list_connections[] = array(
					'id' => $connection['id'],
					'name' => $connection['name'],
					'type' => $connection['type'],
					'filecount' => $filecount
				);
			}
		}
	} else {
		// this means connection has been set, so grab it from the request
		$assets_reponse = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'getassetsforconnection',
				'connection_id' => $connection_id
			),
			'allassets'
		);
		$connection_name = 'root';
		$update_connection_name = AdminHelper::getConnectionName($connection_id);
		if ($update_connection_name) {
			$connection_name = $update_connection_name;
		}
		if (is_array($assets_reponse['payload'])) {
			$list_assets = array(
				'assets' => array(),
				'directories' => array()
			);
			foreach ($assets_reponse['payload'] as $asset) {
				// grab the dirname for the asset
				$tmpdir = dirname($asset['location']);
				if ($tmpdir == $browse_path) {
					// if it matches the browse path then we're working with a file
					$list_assets['assets'][basename($asset['location'])] = $asset;
				} elseif (strpos($tmpdir, $browse_path) !== false || $browse_path == '.') {
					// not an exact match, but we're at root or under the current directory
					$exploded_dir = explode('/',$tmpdir);
					if (!in_array($exploded_dir[$browse_depth],$list_assets['directories'])) {
						$list_assets['directories'][$browse_path . '/' . $exploded_dir[$browse_depth]] = $exploded_dir[$browse_depth];
					}
				}
			}
			ksort($list_assets['directories']);
			ksort($list_assets['assets']);
		}
	}
}

?>