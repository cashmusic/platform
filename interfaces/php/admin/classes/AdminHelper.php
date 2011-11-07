<?php
/**
 * The AdminHelper class provides a single location for various formatting and 
 * quick processing methods needed throughout the admin
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */abstract class AdminHelper  {
	
	/**
	 * Returns metadata for all elements in a keyed array
	 *
	 * @return array | false
	 */public function getElementsData() {
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

	/**
	 * Performs a sessionGetPersistent() CASH Request for the specified variable
	 *
	 */public function getPersistentData($var) {
		$helper_cash_request = new CASHRequest();
		$result = $helper_cash_request->sessionGetPersistent($var);
		unset($helper_cash_request);
		return $result;
	}

	/**
	 * Finds settings matching a specified scope and echoes them out formatted
	 * for a dropdown box in a form
	 *
	 */public function echoSettingsOptions($scope,$selected=false) {
		// get system settings:
		$page_data_object = new CASHSettings(AdminHelper::getPersistentData('cash_effective_user'));
		$settings_types_data = $page_data_object->getSettingsTypes($scope);
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
				$echo_selected = '';
				if ($setting['id'] == $selected) { $echo_selected = ' selected="selected"'; }
				echo '<option value="' . $setting['id'] . '"' . $echo_selected . '>' . $setting['name'] . '</option>';
			}
		}
	}

	/**
	 * Tell it what you need. It makes dropdowns. It's a dropdown robot travelling
	 * at the speed of light — it'll make a supersonic nerd of you. Don't stop it.
	 *
	 * @return array
	 */public function echoFormOptions($base_type,$selected=0) {
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
			case 'venues':
				$plant_name = 'calendar';
				$action_name = 'getallvenues';
				$display_information = 'name';
				break;	
		}
		$echoformoptions_cash_request = new CASHRequest(
			array(
				'cash_request_type' => $plant_name, 
				'cash_action' => $action_name,
				'user_id' => AdminHelper::getPersistentData('cash_effective_user')
			)
		);
		if (is_array($echoformoptions_cash_request->response['payload']) && ($echoformoptions_cash_request->response['status_code'] == 200)) {
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

	public function createdModifiedFromRow($row) {
		$markup = '<div class="smalltext fadedtext created_mod">Created: ' . date('M jS, Y',$row['creation_date']); 
		if ($row['modification_date']) { 
			$markup .= ' (Modified: ' . date('F jS, Y',$row['modification_date']) . ')'; 
		}
		$markup .= '</div>';
		return $markup;
	}

	public function parseMetaData($post_data) {
		$metadata_and_tags = array(
			'metadata_details' => array(),
			'tags_details' => array()
		);
		foreach ($post_data as $key => $value) {
			if (substr($key,0,3) == 'tag' && $value !== '') {
				$metadata_and_tags['tags_details'][] = $value;
				$metadata_and_tags['total_tags'] = count($metadata_and_tags['tags_details']);
			}
			if (substr($key,0,11) == 'metadatakey' && $value !== '') {
				$metadatavalue = $_POST[str_replace('metadatakey','metadatavalue',$key)];
				if ($metadatavalue) {
					$metadata_and_tags['metadata_details'][$value] = $metadatavalue;
				}
			}
		}
		return $metadata_and_tags;
	}

	public function drawCountryCodeUL($selected='USA') {
		$all_codes = array(
			'USA',
			'Brazil',
			'Canada',
			'Czech Republic',
			'France',
			'Germany',
			'Italy',
			'Japan',
			'United Kingdom',
			'',
			'Afghanistan',
			'Albania',
			'Algeria',
			'Andorra',
			'Angola',
			'Antigua &amp; Deps',
			'Argentina',
			'Armenia',
			'Australia',
			'Austria',
			'Azerbaijan',
			'Bahamas',
			'Bahrain',
			'Bangladesh',
			'Barbados',
			'Belarus',
			'Belgium',
			'Belize',
			'Benin',
			'Bhutan',
			'Bolivia',
			'Bosnia Herzegovina',
			'Botswana',
			'Brazil',
			'Brunei',
			'Bulgaria',
			'Burkina',
			'Burundi',
			'Cambodia',
			'Cameroon',
			'Canada',
			'Cape Verde',
			'Central African Rep',
			'Chad',
			'Chile',
			'China',
			'Colombia',
			'Comoros',
			'Congo',
			'Costa Rica',
			'Croatia',
			'Cuba',
			'Cyprus',
			'Czech Republic',
			'Denmark',
			'Djibouti',
			'Dominica',
			'Dominican Republic',
			'East Timor',
			'Ecuador',
			'Egypt',
			'El Salvador',
			'Equatorial Guinea',
			'Eritrea',
			'Estonia',
			'Ethiopia',
			'Fiji',
			'Finland',
			'France',
			'Gabon',
			'Gambia',
			'Georgia',
			'Germany',
			'Ghana',
			'Greece',
			'Grenada',
			'Guatemala',
			'Guinea',
			'Guinea-Bissau',
			'Guyana',
			'Haiti',
			'Honduras',
			'Hungary',
			'Iceland',
			'India',
			'Indonesia',
			'Iran',
			'Iraq',
			'Ireland',
			'Israel',
			'Italy',
			'Ivory Coast',
			'Jamaica',
			'Japan',
			'Jordan',
			'Kazakhstan',
			'Kenya',
			'Kiribati',
			'Korea North',
			'Korea South',
			'Kosovo',
			'Kuwait',
			'Kyrgyzstan',
			'Laos',
			'Latveria',
			'Latvia',
			'Lebanon',
			'Lesotho',
			'Liberia',
			'Libya',
			'Liechtenstein',
			'Lithuania',
			'Luxembourg',
			'Macedonia',
			'Madagascar',
			'Malawi',
			'Malaysia',
			'Maldives',
			'Mali',
			'Malta',
			'Marshall Islands',
			'Mauritania',
			'Mauritius',
			'Mexico',
			'Micronesia',
			'Moldova',
			'Monaco',
			'Mongolia',
			'Montenegro',
			'Morocco',
			'Mozambique',
			'Myanmar, (Burma)',
			'Namibia',
			'Nauru',
			'Nepal',
			'Netherlands',
			'New Zealand',
			'Nicaragua',
			'Niger',
			'Nigeria',
			'Norway',
			'Oman',
			'Pakistan',
			'Palau',
			'Panama',
			'Papua New Guinea',
			'Paraguay',
			'Peru',
			'Philippines',
			'Poland',
			'Portugal',
			'Qatar',
			'Romania',
			'Russian Federation',
			'Rwanda',
			'St Kitts &amp; Nevis',
			'St Lucia',
			'Saint Vincent &amp; the Grenadines',
			'Samoa',
			'San Marino',
			'Sao Tome &amp; Principe',
			'Saudi Arabia',
			'Senegal',
			'Serbia',
			'Seychelles',
			'Sierra Leone',
			'Singapore',
			'Slovakia',
			'Slovenia',
			'Solomon Islands',
			'Somalia',
			'South Africa',
			'Spain',
			'Sri Lanka',
			'Sudan',
			'Suriname',
			'Swaziland',
			'Sweden',
			'Switzerland',
			'Syria',
			'Taiwan',
			'Tajikistan',
			'Tanzania',
			'Thailand',
			'Togo',
			'Tonga',
			'Trinidad &amp; Tobago',
			'Tunisia',
			'Turkey',
			'Turkmenistan',
			'Tuvalu',
			'Uganda',
			'Ukraine',
			'United Arab Emirates',
			'United Kingdom',
			'United States',
			'Uruguay',
			'Uzbekistan',
			'Vanuatu',
			'Vatican City',
			'Venezuela',
			'Vietnam',
			'Yemen',
			'Zambia',
			'Zimbabwe'
		);
		$all_options = '';
		$has_selected = false;
		foreach ($all_codes as $code) {
			$all_options .= '<option value="' . $code . '"';
			if (!$has_selected && $code == $selected) {
				$all_options .= ' selected="selected"';
				$has_selected = true;
			}
			$all_options .= '">' . $code . '</option>';
		}
		return $all_options;
	}

	public function simpleULFromResponse($response,$compact=false,$limit=false) {
		$markup = '';
		if ($response['status_code'] == 200) {
			// spit out the dates
			$markup .= '<ul class="alternating"> ';
			$loopcount = 1;
			foreach ($response['payload'] as $item) {
				$markup .= '<li> ';
				if ($response['status_uid'] == "calendar_getevents_200" || $response['status_uid'] == "calendar_geteventsbetween_200") {
					$event_location = $item['venue_city'] . ', ' . $item['venue_country'];
					if (strtolower($item['venue_country']) == 'usa' || strtolower($item['venue_country']) == 'canada') {
						$event_location = $item['venue_city'] . ', ' . $item['venue_region'];
					}
					if ($compact) {
						if ($item['venue_name']) { 
							$markup .= '<b>' . date('d M',$item['date']) . ': ' . $event_location . '</b> '
									.'<span class="nobr">@ ' . $item['venue_name'] . '</span>'; 
						} else {
							$markup .= '<b>' . date('d M',$item['date']) . ' TBA</b> ';
						}
								
					} else {
						if ($item['venue_name']) { 
							$markup .= '<h4>' . date('d M',$item['date']) . ': ' . $event_location . '</h4> '
									. '<span class="nobr"><b>@ ' . $item['venue_name'] . '</b></span> <span class="fadedtext">' . $item['comments'] . '</span><br />';
						} else {
							$markup .= '<h4>' . date('d M',$item['date']) . ' TBA</h4> '
									. '<span class="fadedtext">' . $item['comments'] . '</span><br />';
						}
					}
					$markup .= '<div class="itemnav">'
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/calendar/events/edit/' . $item['event_id'] . '" class="mininav_flush noblock">Edit</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/calendar/events/delete/' . $item['event_id'] . '" class="needsconfirmation mininav_flush noblock">Delete</a>'
							. '</div>';
					$markup .= '</li>';
				} elseif ($response['status_uid'] == "calendar_getallvenues_200") {
					$venue_location = $item['city'] . ', ' . $item['country'];
					if (strtolower($item['country']) == 'usa' || strtolower($item['country']) == 'canada') {
						$venue_location = $item['city'] . ', ' . $item['region'];
					}
					$markup .= '<b>' . $item['name'] . '</b> '
							.'// <span class="nobr">' . $venue_location . '</span>'; 
					$markup .= '<div class="itemnav">'
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/calendar/venues/edit/' . $item['id'] . '" class="mininav_flush noblock">Edit</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/calendar/venues/delete/' . $item['id'] . '" class="needsconfirmation mininav_flush noblock">Delete</a>'
							. '</div>';
					$markup .= '</li>';
				} elseif ($response['status_uid'] == "people_getlistsforuser_200") {
					$markup .= '<h4>' . $item['name'] . '</h4>'
							. $item['description'] . '<br />'
							. '<div class="itemnav">'
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/view/' . $item['id'] . '" class="mininav_flush">View</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/edit/' . $item['id'] . '" class="mininav_flush">Edit</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/export/' . $item['id'] . '" class="mininav_flush">Export</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/delete/' . $item['id'] . '" class="mininav_flush needsconfirmation">Delete</a>'
							. '</div>';
					$markup .= AdminHelper::createdModifiedFromRow($item);
				} elseif ($response['status_uid'] == "element_getelementsforuser_200") {
					$elements_data = AdminHelper::getElementsData();
					$markup .= '<h4>' . $item['name'];
					if (array_key_exists($item['type'],$elements_data)) {
						$markup .= ' <small class="fadedtext nobr" style="font-weight:normal;"> // ' . $elements_data[$item['type']]->name . '</small> ';
					}
					$markup .= '</h4>'
							. '<div class="itemnav">'
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/elements/view/' . $item['id'] . '" class="mininav_flush">View</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/elements/edit/' . $item['id'] . '" class="mininav_flush">Edit</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/elements/delete/' . $item['id'] . '" class="mininav_flush needsconfirmation">Delete</a>'
							. '</div>';
					$markup .= AdminHelper::createdModifiedFromRow($item);
				}
				$markup .= '</li>';
				if ($loopcount == $limit) { break; }
				$loopcount = $loopcount + 1;
			}
			$markup .= '</ul>';
		} else {
			// no dates matched
			switch($response['action']) {
				case 'getevents':
					$markup .= 'There are no matching dates.';
					break;
				case 'geteventsbetween':
					$markup .= 'There are no matching dates.';
					break;
				case 'getlistsforuser':
					$markup .= 'No lists have been created.';
					break;
				case 'getelementsforuser':
					$markup .= 'No elements were found. None. Zero. Zip. If you\'re looking to add one to the system, <a href="' . ADMIN_WWW_BASE_PATH . '/elements/add/">go here</a>.';
					break;
			}
		}
		return $markup;
	}
} // END class 
?>