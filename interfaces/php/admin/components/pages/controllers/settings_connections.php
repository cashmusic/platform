<?php
$page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
$settings_types_data = $page_data_object->getConnectionTypes();
$settings_for_user = $page_data_object->getAllConnectionsforUser();

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
			'alternating_type' => $alternating_type,
			'service_has_image' => $service_has_image
		);
		$typecount++;
	}
}
$cash_admin->page_data['all_services'] = new ArrayIterator($all_services);

$settings_action = false;
if ($request_parameters) {
	$settings_action = $request_parameters[0];
}

$cash_admin->page_data['action_message'] = false;
if ($settings_action) {
	switch ($settings_action) {
		case 'add':
			$settings_type = $request_parameters[1];
			if ($cash_admin->platform_type == 'single') {
				if (!isset($_POST['dosettingsadd'])) {
					if (array_key_exists($settings_type, $settings_types_data)) {
						$cash_admin->page_data['state_markup'] = '<h3>Connect to ' . $settings_types_data[$settings_type]['name'] . '</h3><p>' . $settings_types_data[$settings_type]['description'] . '</p>';

						$cash_admin->page_data['state_markup'] .= '<form method="post" action="">'
							. '<input type="hidden" name="dosettingsadd" value="makeitso" />'
							. '<input type="hidden" name="settings_type" value="' . $settings_type . '" />'
							. '<label for="settings_name">Name</label><br />'
							. '<input type="text" id="settings_name" name="settings_name" placeholder="Give It A Name" />'
							. '<div class="row_seperator tall">.</div>';
							
							foreach ($settings_types_data[$settings_type]['dataTypes'][$cash_admin->platform_type] as $key => $data) {
								$cash_admin->page_data['state_markup'] .= '<label for="' . $key . '">' . $key . '</label><br />'
									. '<input type="text" id="' . $key . '" name="' . $key . '" placeholder="' . ucfirst($key) . '" />'
									. '<div class="row_seperator">.</div>';
							}

							$cash_admin->page_data['state_markup'] .= '<div class="row_seperator">.</div><br />'
								. '<div><input class="button" type="submit" value="Add The Connection" /></div>'
								. '</form>';
					} else {
						$cash_admin->page_data['state_markup'] = '<h3>Error</h3><p>The requested setting type could not be found.</p>';
					}
				} else {
					$settings_data_array = array();
					foreach ($settings_types_data[$settings_type]['dataTypes'][$cash_admin->platform_type] as $key => $data) {
						$settings_data_array[$key] = $_POST[$key];
					}
					$result = $page_data_object->setSettings(
						$_POST['settings_name'],
						$_POST['settings_type'],
						$settings_data_array
					);
					if ($result) {
						$cash_admin->page_data['action_message'] = '<b>Success.</b> Everything was added successfully. You\'ll see the new connection below.';
					} else {
						$cash_admin->page_data['action_message'] = '<b>Error.</b> Something went wrong. Please make sure you\'re using a unique name for this connection. Not only is that just smart, it\'s required.';
					}
				}
			} else {
				// oauthy
				if (isset($_POST['dosettingsadd'])) {
					// grab the stuff we need from $_POST then strip it out, pass the rest as data to store with the connection
					$settings_name = $_POST['settings_name'];
					$settings_type = $_POST['settings_type'];
					unset($_POST['settings_name'],$_POST['settings_type'],$_POST['dosettingsadd']);
					$result = $page_data_object->setSettings(
						$settings_name,
						$settings_type,
						$_POST
					);
					if ($result) {
						AdminHelper::formSuccess('Success. Connection added. You\'ll see it below.','/settings/connections/');
					} else {
						AdminHelper::formFailure('Error. Something just didn\'t work right.','/settings/connections/');
					}
				} else {
					$finalize = false;
					if (isset($request_parameters[2])) {
						if ($request_parameters[2] == 'finalize') {
							$finalize = true;
						}
					}
					$seed_name = $settings_types_data[$settings_type]['seed'];
					if (!$finalize) {
						$return_url = rtrim(CASHSystem::getCurrentURL(),'/') . '/finalize';
						// Here's a really fucked up way of calling $seed_name::getRedirectMarkup($return_url) [5.2+ compatibility]
						$cash_admin->page_data['state_markup'] = call_user_func($seed_name . '::getRedirectMarkup', $return_url);
					} else {
						// Here's a really fucked up way of calling $seed_name::handleRedirectReturn($_REQUEST) [5.2+ compatibility]
						$connections_base_uri = rtrim(str_replace($request_parameters,'',CASHSystem::getCurrentURL()),'/');
						$_REQUEST['connections_base_uri'] = $connections_base_uri;
						$cash_admin->page_data['state_markup'] = call_user_func($seed_name . '::handleRedirectReturn', $_REQUEST);
						//$cash_admin->page_data['state_markup'] = $seed_name::handleRedirectReturn($_GET);
					}
				}
			}
			break;
		case 'edit':
			$connection_id = $request_parameters[1];
			$settings_name = $request_parameters[2];
			$settings_type = $request_parameters[3];
			$settings_details = $page_data_object->getConnectionSettings($connection_id);
			if (!isset($_POST['dosettingsedit'])) {
				if ($settings_details) {
					$cash_admin->page_data['state_markup'] = '<h3>Edit ' . $settings_name . '</h3>'
					 . '<form method="post" action="">'
					 .		'<input type="hidden" name="dosettingsedit" value="makeitso" />'
					 .		'<input type="hidden" name="connection_id" value="' . $connection_id . '" />'
					 .		'<input type="hidden" name="settings_type" value="' . $settings_type . '" />'
					 .		'<label for="settings_name">Name</label><br />'
					 .		'<input type="text" id="settings_name" name="settings_name" value="' . $settings_name . '" />'
					 .	'<div class="row_seperator tall">.</div>';

						foreach ($settings_types_data[$settings_type]['dataTypes'][$cash_admin->platform_type] as $key => $data) {
							$cash_admin->page_data['state_markup'] .=  '<label for="' . $key . '">' . $key . '</label><br />'
								. '<input type="text" id="' . $key . '" name="' . $key . '" value="' . $settings_details[$key] . '" />'
								. '<div class="row_seperator">.</div>';
						}
						$cash_admin->page_data['state_markup'] .= '<div class="row_seperator">.</div><br />'
							. '<div><input class="button" type="submit" value="Edit The Connection" /></div>'
							. '</form>';
				} else {
					$cash_admin->page_data['action_message'] = '<b>Error.</b> The requested connection could not be found.';
				}
			} else {
				$settings_data_array = array();
				foreach ($settings_types_data[$settings_type]['dataTypes'][$cash_admin->platform_type] as $key => $data) {
					$settings_data_array[$key] = $_POST[$key];
				}
				$result = $page_data_object->setSettings(
					$_POST['settings_name'],
					$_POST['settings_type'],
					$settings_data_array,
					$_POST['connection_id']
				);
				if ($result) {
					$cash_admin->page_data['action_message'] = '<b>Success.</b> All changed. See connection below.';
				} else {
					$cash_admin->page_data['action_message'] = '<b>Error.</b> Something went wrong.';
				}
			}
			break;
		case 'delete':
			$connection_id = $request_parameters[1];
			$result = $page_data_object->deleteSettings($connection_id);
			if ($result) {
				$cash_admin->page_data['action_message'] = '<b>Success.</b> All gone. Sad.';
			} else {
				$cash_admin->page_data['action_message'] = '<b>Error.</b> Something went wrong.';
			}
			break;
	}	
}
if (!$settings_action || isset($_POST['dosettingsadd']) || isset($_POST['dosettingsedit']) || $settings_action == 'delete' ) {
	$cash_admin->page_data['state_markup'] = '<h3>Current connections:</h3>'
		. '<p>Here are the settings that have already been added:</p>';
	$settings_for_user = $page_data_object->getAllConnectionsforUser();
	if (is_array($settings_for_user)) {
		foreach ($settings_for_user as $key => $data) {
			$cash_admin->page_data['state_markup'] .= '<div class="callout">'
				. '<h4>' . $data['name'] . '</h4>';

			if (array_key_exists($data['type'],$settings_types_data)) {
				$cash_admin->page_data['state_markup'] .= '<b>' . $settings_types_data[$data['type']]['name'] . '</b> ';
			} 

			$cash_admin->page_data['state_markup'] .= '&nbsp; <span class="smalltext fadedtext nobr">Created: ' . date('M jS, Y',$data['creation_date']);
			if ($data['modification_date']) { 
				$cash_admin->page_data['state_markup'] .=  ' (Modified: ' . date('F jS, Y',$data['modification_date']) . ')'; 
			}

			$cash_admin->page_data['state_markup'] .= '</span>'
				. '<div class="itemnav">';
				if ($cash_admin->platform_type == 'single') {
					$cash_admin->page_data['state_markup'] .=  '<a href="' . ADMIN_WWW_BASE_PATH . '/settings/connections/edit/' . $data['id'] . '/' . $data['name'] . '/' . $data['type'] . '/" class="mininav_flush">Edit</a> ';
				}
				$cash_admin->page_data['state_markup'] .= '<a href="' . ADMIN_WWW_BASE_PATH . '/settings/connections/delete/' . $data['id'] . '/" class="needsconfirmation mininav_flush">Delete</a>'
				. '</div>'
				. '</div>';
		}
	} else {
		$cash_admin->page_data['state_markup'] .= 'No settings have been added.';
	}
}

$cash_admin->setPageContentTemplate('settings_connections');
?>