<?php
/**
 * The AdminHelper class provides a single location for various formatting and 
 * quick processing methods needed throughout the admin
 * 
 * Most functions that are simple/static framework wrappers or data formatting should go here
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

	public static function doLogin($email_address,$password,$require_admin=true,$browserid_assertion=false) {
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validatelogin',
				'address' => $email_address, 
				'password' => $password,
				'require_admin' => $require_admin,
				'browserid_assertion' => $browserid_assertion
			)
		);
		return $login_request->response['payload'];
	}

	/**********************************************
	 *
	 * PAGE/UI RENDERING DETAILS
	 *
	 *********************************************/

	public static function buildSectionNav() {
		$pages_array = json_decode(file_get_contents(dirname(__FILE__).'/../components/menu/menu_en.json'),true);
		$endpoint = str_replace('_','/',BASE_PAGENAME);
		$endpoint_parts = explode('/',$endpoint);
		$section_base = $pages_array[$endpoint_parts[0]];
		$section_pages = array();
		foreach ($pages_array as $page_endpoint => $page) {
			if (strrpos($page_endpoint,$endpoint_parts[0]) !== false) {
				$section_pages[$page_endpoint] = $page;
			}
		}
		if (count($section_pages) > 1) {
			$menustr = '<a href="'. ADMIN_WWW_BASE_PATH . '/' . $endpoint_parts[0] . '/" class="pagemenutitle">' . $section_base['page_name'] . '</a>';
			$menustr .= '<ul class="pagebasemenu">';
			foreach ($section_pages as $page_endpoint => $page) {
				$menulevel = substr_count($page_endpoint, '/');
				if ($menulevel == 1 && !isset($page['hide'])) { // only show top-level menu items
					if (str_replace('/','_',$page_endpoint) == BASE_PAGENAME) {
						$menustr .= "<li><a href=\"" . ADMIN_WWW_BASE_PATH . "/$page_endpoint/\" style=\"color:#babac4;\"><span class=\"icon {$page['menu_icon']}\"></span> {$page['page_name']}</a></li>";
					} else {
						$menustr .= "<li><a href=\"" . ADMIN_WWW_BASE_PATH . "/$page_endpoint/\"><span class=\"icon {$page['menu_icon']}\"></span> {$page['page_name']}</a></li>";
					}
				}
			}
			$menustr .= '</ul>';
			return $menustr;
		} else {
			return false;
		}
	}

	public static function getPageTitle() {
		$pages_array = json_decode(file_get_contents(dirname(__FILE__).'/../components/menu/menu_en.json'),true);
		$endpoint = str_replace('_','/',BASE_PAGENAME);
		if (isset($pages_array[$endpoint])) {
			$endpoint_parts = explode('/',$endpoint);
			$current_title = '';
			if (count($endpoint_parts) > 1) {
				$current_title .= $pages_array[$endpoint_parts[0]]['page_name'] . ': ';
			}
			$current_title .= $pages_array[$endpoint]['page_name'];
			return $current_title;
		}
		return 'CASH Music';
	}

	public static function getPageTipsString() {
		$tips_array = json_decode(file_get_contents(dirname(__FILE__).'/../components/text/en/pagetips.json'),true);
		$endpoint = str_replace('_','/',BASE_PAGENAME);
		if (isset($tips_array[$endpoint])) {
			if ($tips_array[$endpoint]) {
				return $tips_array[$endpoint];
			}
		}
		return $tips_array['default'];
	}

	/**********************************************
	 *
	 * CONNECTION DETAILS
	 *
	 *********************************************/
	/**
	 * Finds settings matching a specified scope and echoes them out formatted
	 * for a dropdown box in a form
	 *
	 */public static function echoConnectionsOptions($scope,$selected=false) {
		// get system settings:
		$page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
		$applicable_settings_array = $page_data_object->getConnectionsByScope($scope);

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
	 * Returns the name given to a specific Connection
	 *
	 */public static function getConnectionName($connection_id) {
		$page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
		$connection_name = false;
		$connection_details = $page_data_object->getConnectionDetails($connection_id);
		if ($connection_details) {
			$connection_name = $connection_details['name'];
		}
		return $connection_name;
	}

	/**********************************************
	 *
	 * ELEMENT DETAILS
	 *
	 *********************************************/

	/**
	 * Returns metadata for all elements in a keyed array
	 *
	 * @return array | false
	 */public static function getElementsData() {
		$elements_dirname = CASH_PLATFORM_ROOT.'/elements';
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

	/**********************************************
	 *
	 * SIMPLE DATA FORMATTING
	 *
	 *********************************************/

	public static function createdModifiedFromRow($row,$top=false) {
		$addtoclass = '';
		if ($top) { $addtoclass = '_top'; }
		$markup = '<div class="smalltext fadedtext created_mod' . $addtoclass . '">Created: ' . date('M jS, Y',$row['creation_date']); 
		if ($row['modification_date']) { 
			$markup .= ' (Modified: ' . date('F jS, Y',$row['modification_date']) . ')'; 
		}
		$markup .= '</div>';
		return $markup;
	}

	/**
	 * Spit out human readable byte size
	 * swiped from comments: http://us2.php.net/manual/en/function.memory-get-usage.php
	 *
	 * @param $bytes (int)
	 * @param $precision (int)
	 * @return string
	 */function bytesToSize($bytes, $precision = 2) {
	    $unit = array('B','KB','MB','GB','TB','PB','EB');
		return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $precision) . ' ' . $unit[$i];
	}

	/**********************************************
	 *
	 * MISCELLANEOUS
	 *
	 *********************************************/

	public static function parseMetaData($post_data) {
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

	/**
	 * Performs a sessionGet() CASH Request for the specified variable
	 *
	 */public static function getPersistentData($var) {
		$helper_cash_request = new CASHRequest(null);
		$result = $helper_cash_request->sessionGet($var);
		unset($helper_cash_request);
		return $result;
	}

	/**********************************************
	 *
	 * FORM HELPER FUNCTIONS
	 *
	 *********************************************/

	public static function drawCountryCodeUL($selected='USA') {
		$all_codes = array(
			'USA','Brazil','Canada','Czech Republic','France','Germany','Italy','Japan','United Kingdom',
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
			$all_options .= '>' . $code . '</option>';
		}
		return $all_options;
	}

	public static function drawTimeZones($selected='US/Pacific') {
		$all_zones = array(
			'US/Alaska',
			'US/Arizona',
			'US/Central',
			'US/East-Indiana',
			'US/Eastern',
			'US/Hawaii',
			'US/Mountain',
			'US/Pacific',
			'US/Samoa',
			'Africa/Cairo',
			'Africa/Casablanca',
			'Africa/Harare',
			'Africa/Monrovia',
			'Africa/Nairobi',
			'America/Bogota',
			'America/Buenos_Aires',
			'America/Caracas',
			'America/Chihuahua',
			'America/La_Paz',
			'America/Lima',
			'America/Mazatlan',
			'America/Mexico_City',
			'America/Monterrey',
			'America/Santiago',
			'America/Tijuana',
			'Asia/Almaty',
			'Asia/Baghdad',
			'Asia/Baku',
			'Asia/Bangkok',
			'Asia/Chongqing',
			'Asia/Dhaka',
			'Asia/Hong_Kong',
			'Asia/Irkutsk',
			'Asia/Jakarta',
			'Asia/Jerusalem',
			'Asia/Kabul',
			'Asia/Kamchatka',
			'Asia/Karachi',
			'Asia/Kathmandu',
			'Asia/Kolkata',
			'Asia/Krasnoyarsk',
			'Asia/Kuala_Lumpur',
			'Asia/Kuwait',
			'Asia/Magadan',
			'Asia/Muscat',
			'Asia/Novosibirsk',
			'Asia/Riyadh',
			'Asia/Seoul',
			'Asia/Singapore',
			'Asia/Taipei',
			'Asia/Tashkent',
			'Asia/Tbilisi',
			'Asia/Tehran',
			'Asia/Tokyo',
			'Asia/Ulaanbaatar',
			'Asia/Urumqi',
			'Asia/Vladivostok',
			'Asia/Yakutsk',
			'Asia/Yekaterinburg',
			'Asia/Yerevan',
			'Atlantic/Azores',
			'Atlantic/Cape_Verde',
			'Atlantic/Stanley',
			'Australia/Adelaide',
			'Australia/Brisbane',
			'Australia/Canberra',
			'Australia/Darwin',
			'Australia/Hobart',
			'Australia/Melbourne',
			'Australia/Perth',
			'Australia/Sydney',
			'Canada/Atlantic',
			'Canada/Newfoundland',
			'Canada/Saskatchewan',
			'Europe/Amsterdam',
			'Europe/Athens',
			'Europe/Belgrade',
			'Europe/Berlin',
			'Europe/Bratislava',
			'Europe/Brussels',
			'Europe/Bucharest',
			'Europe/Budapest',
			'Europe/Copenhagen',
			'Europe/Dublin',
			'Europe/Helsinki',
			'Europe/Istanbul',
			'Europe/Kiev',
			'Europe/Lisbon',
			'Europe/Ljubljana',
			'Europe/London',
			'Europe/Madrid',
			'Europe/Minsk',
			'Europe/Moscow',
			'Europe/Paris',
			'Europe/Prague',
			'Europe/Riga',
			'Europe/Rome',
			'Europe/Sarajevo',
			'Europe/Skopje',
			'Europe/Sofia',
			'Europe/Stockholm',
			'Europe/Tallinn',
			'Europe/Vienna',
			'Europe/Vilnius',
			'Europe/Volgograd',
			'Europe/Warsaw',
			'Europe/Zagreb',
			'Greenland',
			'Pacific/Auckland',
			'Pacific/Fiji',
			'Pacific/Guam',
			'Pacific/Midway',
			'Pacific/Port_Moresby'
		);
		$all_options = '';
		$has_selected = false;
		foreach ($all_zones as $zone) {
			$all_options .= '<option value="' . $zone . '"';
			if (!$has_selected && $zone == $selected) {
				$all_options .= ' selected="selected"';
				$has_selected = true;
			}
			$all_options .= '>' . $zone . '</option>';
		}
		return $all_options;
	}

	public static function simpleULFromResponse($response,$compact=false,$limit=false) {
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
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/calendar/events/edit/' . $item['event_id'] . '" class="mininav_flush noblock"><span class="icon pen"></span> Edit</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/calendar/events/delete/' . $item['event_id'] . '" class="needsconfirmation mininav_flush noblock"><span class="icon x_alt"></span> Delete</a>'
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
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/calendar/venues/edit/' . $item['id'] . '" class="mininav_flush noblock"><span class="icon pen"></span> Edit</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/calendar/venues/delete/' . $item['id'] . '" class="needsconfirmation mininav_flush noblock"><span class="icon x_alt"></span> Delete</a>'
							. '</div>';
					$markup .= '</li>';
				} elseif ($response['status_uid'] == "people_getlistsforuser_200") {
					$markup .= '<h4>' . $item['name'] . '</h4>'
							. AdminHelper::createdModifiedFromRow($item,true)
							. $item['description'] . '<br />'
							. '<div class="itemnav">'
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/view/' . $item['id'] . '" class="mininav_flush"><span class="icon magnifying_glass"></span> View</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/edit/' . $item['id'] . '" class="mininav_flush"><span class="icon pen"></span> Edit</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/export/' . $item['id'] . '" class="mininav_flush"><span class="icon download"></span> Export</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/people/lists/delete/' . $item['id'] . '" class="mininav_flush needsconfirmation"><span class="icon x_alt"></span> Delete</a>'
							. '</div>';
				} elseif ($response['status_uid'] == "commerce_getitemsforuser_200") {
					$markup .= '<h4>' . $item['name'] . '</h4>'
							. AdminHelper::createdModifiedFromRow($item,true)
							. '<div class="itemnav">'
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/commerce/items/edit/' . $item['id'] . '" class="mininav_flush"><span class="icon pen"></span> Edit</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/commerce/items/delete/' . $item['id'] . '" class="mininav_flush needsconfirmation"><span class="icon x_alt"></span> Delete</a>'
							. '</div>';
				} elseif ($response['status_uid'] == "element_getelementsforuser_200") {
					$elements_data = AdminHelper::getElementsData();
					$markup .= '<h4>' . $item['name'];
					if (array_key_exists($item['type'],$elements_data)) {
						$markup .= ' <small class="fadedtext nobr" style="font-weight:normal;"> // ' . $elements_data[$item['type']]->name . '</small> ';
					}
					$markup .= '</h4>'
							. '<div>'
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/elements/view/' . $item['id'] . '" class="mininav_flush"><span class="icon magnifying_glass"></span> Details</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/elements/edit/' . $item['id'] . '" class="mininav_flush"><span class="icon pen"></span> Edit</a> '
							. '<a href="' . ADMIN_WWW_BASE_PATH . '/elements/delete/' . $item['id'] . '" class="mininav_flush needsconfirmation"><span class="icon x_alt"></span> Delete</a>'
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

	/**
	 * Tell it what you need. It makes dropdowns. It's a dropdown robot travelling
	 * at the speed of light — it'll make a supersonic nerd of you. Don't stop it.
	 *
	 * @return array
	 */public static function echoFormOptions($base_type,$selected=0,$range=false) {
		switch ($base_type) {
			case 'assets':
				$plant_name = 'asset';
				$action_name = 'getassetsforuser';
				$display_information = 'title';
				if ($range) {
					if (!in_array($selected,$range)) {
						$range[] = $selected;
					}
				}
				break;
			case 'people_lists':
				$plant_name = 'people';
				$action_name = 'getlistsforuser';
				$display_information = 'name';
				break;
			case 'venues':
				$plant_name = 'calendar';
				$action_name = 'getallvenues';
				$display_information = 'name';
				break;	
			case 'items':
				$plant_name = 'commerce';
				$action_name = 'getitemsforuser';
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
				$doloop = true;
				if ($range) {
					if (!in_array($item['id'],$range)) {
						$doloop = false;
					}
				}
				if ($doloop) {
					$selected_string = '';
					if ($item['id'] == $selected) { 
						$selected_string = ' selected="selected"';
					}
					echo '<option value="' . $item['id'] . '"' . $selected_string . '>' . $item[$display_information] . '</option>';
				}
			}
		}
		unset($echoformoptions_cash_request);
	}
	
} // END class 
?>