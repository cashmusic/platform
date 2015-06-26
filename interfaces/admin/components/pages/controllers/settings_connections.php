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
			$seed_name = $settings_types_data[$settings_type]['seed'];
			if ($cash_admin->platform_type == 'single') {
				if (!isset($_POST['dosettingsadd'])) {
					if (array_key_exists($settings_type, $settings_types_data)) {
						$cash_admin->page_data['state_markup'] = '<h4>' . $settings_types_data[$settings_type]['name'] . '</h4><p>' . $settings_types_data[$settings_type]['instructions'] . '</p>';

						$cash_admin->page_data['state_markup'] .= '<form method="post" action="">'
							. '<input type="hidden" name="dosettingsadd" value="makeitso" />'
							. '<input type="hidden" name="settings_type" value="' . $settings_type . '" />'
							. '<label for="settings_name">Connection name</label>'
							. '<input type="text" id="settings_name" name="settings_name" placeholder="Give It A Name" /><br />';

							foreach ($settings_types_data[$settings_type]['dataTypes'][$cash_admin->platform_type] as $key => $data) {
								$cash_admin->page_data['state_markup'] .= '<label for="' . $key . '">' . $key . '</label>'
									. '<input type="text" id="' . $key . '" name="' . $key . '" placeholder="' . ucfirst($key) . '" />';
							}

							$cash_admin->page_data['state_markup'] .= '<div class="row_seperator"></div><br />'
								. '<div><input class="button" type="submit" value="Add The Connection" /></div>'
								. '</form>';
					} else {
						$cash_admin->page_data['state_markup'] = '<h4>Error</h4><p>The requested setting type could not be found.</p>';
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
						// postConnection hook
						if (method_exists($seed_name,'postConnection')) {
							$_POST['settings_id'] = $result;
							$_POST['user_id'] = $cash_admin->effective_user_id;
							$_POST['settings_type'] = $settings_type;
							$_POST['settings_name'] = $settings_name;
							$seed_name::postConnection($_POST);
						}
						$cash_admin->page_data['action_message'] = '<strong>Success.</strong> Everything was added successfully. You\'ll see it in your list of connections.';
					} else {
						$cash_admin->page_data['action_message'] = '<strong>Error.</strong> Something went wrong. Please make sure you\'re using a unique name for this connection. Not only is that just smart, it\'s required.';
					}
				}
			} else {
				// oauthy ...oauthish?
				$cash_admin->page_data['service_selected'] = true;
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
						// postConnection hook
						if (method_exists($seed_name,'postConnection')) {
							$_POST['settings_id'] = $result;
							$_POST['user_id'] = $cash_admin->effective_user_id;
							$_POST['settings_type'] = $settings_type;
							$_POST['settings_name'] = $settings_name;
							$seed_name::postConnection($_POST);
						}
						AdminHelper::formSuccess('Success. Connection added. You\'ll see it in your list of connections.','/settings/connections/');
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
						// PHP <= 5.2 >>>> $cash_admin->page_data['state_markup'] = call_user_func($seed_name . '::getRedirectMarkup', $return_url);
						$cash_admin->page_data['state_markup'] = $seed_name::getRedirectMarkup($return_url);
					} else {
						$connections_base_uri = rtrim(str_replace($request_parameters,'',CASHSystem::getCurrentURL()),'/');
						$_REQUEST['connections_base_uri'] = $connections_base_uri;
						// PHP <= 5.2 >>>> $cash_admin->page_data['state_markup'] = call_user_func($seed_name . '::handleRedirectReturn', $_REQUEST);
						$cash_admin->page_data['state_markup'] = $seed_name::handleRedirectReturn($_REQUEST);
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
					$cash_admin->page_data['state_markup'] = '<h4>Edit ' . $settings_name . '</h4><p>' . $settings_types_data[$settings_type]['description'] . '</p>'
					 . '<form method="post" action="">'
					 .		'<input type="hidden" name="dosettingsedit" value="makeitso" />'
					 .		'<input type="hidden" name="connection_id" value="' . $connection_id . '" />'
					 .		'<input type="hidden" name="settings_type" value="' . $settings_type . '" />'
					 .		'<label for="settings_name">Connection name</label>'
					 .		'<input type="text" id="settings_name" name="settings_name" value="' . $settings_name . '" /><br />';

						foreach ($settings_types_data[$settings_type]['dataTypes'][$cash_admin->platform_type] as $key => $data) {
							$cash_admin->page_data['state_markup'] .=  '<label for="' . $key . '">' . $key . '</label>'
								. '<input type="text" id="' . $key . '" name="' . $key . '" value="' . $settings_details[$key] . '" />';
						}
						$cash_admin->page_data['state_markup'] .= '<div class="row_seperator"></div><br />'
							. '<div><input class="button" type="submit" value="Edit The Connection" /></div>'
							. '</form>';
				} else {
					$cash_admin->page_data['action_message'] = '<strong>Error.</strong> The requested connection could not be found.';
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
					$cash_admin->page_data['action_message'] = '<strong>Success.</strong> All changed. See connection below.';
				} else {
					$cash_admin->page_data['action_message'] = '<strong>Error.</strong> Something went wrong.';
				}
			}
			break;
		case 'delete':
			$connection_id = $request_parameters[1];
			$result = $page_data_object->deleteSettings($connection_id);
			if ($result) {
				AdminHelper::formSuccess('Success. Deleted. Sad.','/');
				//$cash_admin->page_data['action_message'] = '<strong>Success.</strong> All gone. Sad.';
			} else {
				AdminHelper::formFailure('Something went wrong.','/');
				//$cash_admin->page_data['action_message'] = '<strong>Error.</strong> Something went wrong.';
			}
			break;
	}
}
if (!$settings_action || isset($_POST['dosettingsadd']) || isset($_POST['dosettingsedit']) || $settings_action == 'delete' ) {
	$cash_admin->page_data['state_markup'] = '<h4>Current connections:</h4>'
		. '<p>Here are the settings that have already been added:</p>';
	$settings_for_user = $page_data_object->getAllConnectionsforUser();
	if (is_array($settings_for_user)) {
		foreach ($settings_for_user as $key => $data) {
			$cash_admin->page_data['state_markup'] .= '<div class="callout">'
				. '<h6>' . $data['name'] . '</h6>';

			if (array_key_exists($data['type'],$settings_types_data)) {
				$cash_admin->page_data['state_markup'] .= '<p><strong>' . $settings_types_data[$data['type']]['name'] . '</strong>';
			}

			$cash_admin->page_data['state_markup'] .= '&nbsp; <span class="fadedtext nobr">Created: ' . date('M jS, Y',$data['creation_date'])  . '</p>';
			if ($data['modification_date']) {
				$cash_admin->page_data['state_markup'] .=  ' (Modified: ' . date('F jS, Y',$data['modification_date']) . ')';
			}

			$cash_admin->page_data['state_markup'] .= '</span>'
				. '<div class="itemnav acation">';
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

$cash_admin->page_data['ui_title'] = '';
$cash_admin->setPageContentTemplate('settings_connections');
?>
