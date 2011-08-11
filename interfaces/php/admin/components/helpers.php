<?php
function getElementsData() {
	$elements_dirname = ADMIN_BASE_PATH.'/components/elements';
	if ($elements_dir = opendir($elements_dirname)) {
		$tmpArray = array();
		while (false !== ($dir = readdir($elements_dir))) {
			if (substr($dir,0,1) != "." && is_dir($elements_dirname . '/' . $dir)) {
				$tmpKey = strtolower($dir);
				if (@file_exists($elements_dirname . '/' . $dir . '/metadata.json')) {
					$tmpValue = json_decode(@file_get_contents($elements_dirname . '/' . $dir . '/metadata.json'));
					if ($tmpValue) {
						$tmpArray["$tmpKey"] = $tmpValue;
					}
				}
			}
		}
		closedir($elements_dir);
		if (count($tmpArray)) {
			return $tmpArray;
		} else {
			return false;
		}
	} else {
		echo 'not dir';
		return false;
	}
}

function getPersistentData($var) {
	$helper_cash_request = new CASHRequest();
	$result = $helper_cash_request->sessionGetPersistent($var);
	unset($helper_cash_request);
	return $result;
}

function echoSettingsOptions($filter) {
	// get system settings:
	$page_data_object = new CASHSettings(getPersistentData('cash_effective_user'));
	$settings_types_data = $page_data_object->getSettingsTypes($filter);
	$applicable_settings_array = false;
	foreach ($settings_types_data as $type_data) {
		$result = $page_data_object->getSettingsByType($type_data->type);
		if ($result) {
			if (!$applicable_settings_array) { $applicable_settings_array = array(); }
			$applicable_settings_array = $applicable_settings_array + $result;
		}
	}
	
	// echo out the proper dropdown bits
	if ($applicable_settings_array) {
		$settings_count = 1;
		foreach ($applicable_settings_array as $setting) {
			echo '<option value="' . $setting['id'] . '">' . $setting['name'] . '</option>';
		}
	}
}

function echoFormOptions($base_type,$selected=0) {
	switch ($base_type) {
		case 'assets':
			$plant_name = 'asset';
			$action_name = 'getassetsforuser';
			$display_information = 'title';
			break;
		case 'user_lists':
			$plant_name = 'people';
			$action_name = 'getlistsforuser';
			$display_information = 'name';
			break;	
	}
	$echoformoptions_cash_request = new CASHRequest(
		array(
			'cash_request_type' => $plant_name, 
			'cash_action' => $action_name,
			'user_id' => getPersistentData('cash_effective_user')
		)
	);
	if (is_array($echoformoptions_cash_request->response['payload'])) {
		foreach ($echoformoptions_cash_request->response['payload'] as $item) {
			$selected_string = '';
			if ($item['id'] == $selected) { 
				$selected_string = ' selected="selected"';
			}
			echo '<option value="' . $item['id'] . '"' . $selected_string . '>' . $item[$display_information] . '</option>';
		}
	}
	unset($echoformoptions_cash_request);
}
?>