<?php
/***************************************************************************************************
 *
 * Default CASH admin simple mode app controller
 *
 * @package admin.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 ***************************************************************************************************/


/***************************************************************************************************
 *
 * FUNCTIONS
 *
 ***************************************************************************************************/
function getUploadParameters($id) {
	global $cash_admin;
	$param_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'getuploadparameters',
			'connection_id' => $id
		)
	);
	if (is_array($param_response['payload'])) {
		//$cash_admin->page_data = array_merge($cash_admin->page_data,$param_response['payload']);
		return $param_response['payload'];
	}
}

function getConnectionId($connection_name) {
	global $cash_admin;
	$page_data_object = new CASHConnection($cash_admin->effective_user_id);
	$settings_for_user = $page_data_object->getAllConnectionsforUser();
	if (is_array($settings_for_user)) {
		foreach ($settings_for_user as $key => $data) {
			if ($data['name'] == $connection_name) {
				return $data['id'];
			}
		}
	}
	return false;
}


/***************************************************************************************************
 *
 * PARSE AND STORE CURRENT LOCATION, HANDLE INITIAL TEMPLATE SELECTION
 *
 ***************************************************************************************************/
$views_dir = dirname(__FILE__) . '/simplemode';
$handle_oauth_return = false;

// figure out current state
if (count($request_parameters)) {
	$current_state = implode('_', $request_parameters);
} else {
	if (!isset($current_settings['simple_mode_data'])) {
		$current_state = 'begin';
	} else {
		$current_state = $current_settings['simple_mode_data']['current_state'];
	}
}
$current_state = strtolower($current_state);

// remember where we are
if (!isset($current_settings['simple_mode_data'])) {
	$current_settings['simple_mode_data'] = array(
		'current_state' => 'begin'
	);
	$cash_admin->setUserSettings($current_settings);
} else {
	if ($current_settings['simple_mode_data']['current_state'] !== $current_state) {
		// $current_state == 'add_com.google.drive_finalize' means we've got
		// a return from google drive — don't set a new state, but do set the
		// oauth return as true
		if ($current_state == 'add_com.google.drive_finalize' || 
			$current_state == 'add_com.mailchimp_finalize' ||
			$current_state == 'add_com.paypal_finalize'
		) {
			$handle_oauth_return = true;
			$current_state = $current_settings['simple_mode_data']['current_state'];
		} else {
			if ($current_state !== 'es_export') {
				$current_settings['simple_mode_data']['current_state'] = $current_state;
				$cash_admin->setUserSettings($current_settings);
			}
		}
	}
}


/***************************************************************************************************
 *
 * ACTUALLY DO SOMETHING WITH THE CURRENT STATE
 * (and yeah i know this should be a switch, but elsifs are just more legible)
 *
 ***************************************************************************************************/
if ($current_state == 'advanced') {
	/***********************************************************************************************
	 * 'advanced'
	 ***********************************************************************************************/
	$current_settings['use_simple_mode'] = false;
	unset($current_settings['simple_mode_data']);
	$cash_admin->setUserSettings($current_settings);
	header('Location: ' . ADMIN_WWW_BASE_PATH);
	exit(); // i know this never fires. i just...just...have to.
} else if ($current_state == 'es_1' || $current_state == 'ds_1') { // first page
	/***********************************************************************************************
	 * 'es_1'
	 ***********************************************************************************************/
	$page_data_object = new CASHConnection($cash_admin->effective_user_id);
	$settings_types_data = $page_data_object->getConnectionTypes();

	$seed_name = $settings_types_data['com.google.drive']['seed'];
	if (!$handle_oauth_return) {
		$return_url = rtrim(CASHSystem::getCurrentURL(),'/') . '/finalize';
		// Here's a really fucked up way of calling $seed_name::getRedirectMarkup($return_url) [5.2+ compatibility]
		$cash_admin->page_data['state_markup'] = call_user_func($seed_name . '::getRedirectMarkup', $return_url);
	} else {
		// Here's a really fucked up way of calling $seed_name::handleRedirectReturn($_REQUEST) [5.2+ compatibility]
		$connections_base_uri = rtrim(str_replace($request_parameters,'',CASHSystem::getCurrentURL()),'/');
		$_REQUEST['connections_base_uri'] = $connections_base_uri;
		$_REQUEST['return_result_directly'] = true;
		$connection_id = call_user_func($seed_name . '::handleRedirectReturn', $_REQUEST);

		if ($connection_id) {
			$current_settings['simple_mode_data']['googledrive_connection_id'] = $connection_id;
			if ($current_state == 'es_1') {
				$current_settings['simple_mode_data']['current_state'] = 'es_2';
				$cash_admin->setUserSettings($current_settings);
				$current_state = 'es_2';
			} else {
				$current_settings['simple_mode_data']['current_state'] = 'ds_2';
				$cash_admin->setUserSettings($current_settings);
				$current_state = 'ds_2';
			}

			$parameters = getUploadParameters($current_settings['simple_mode_data']['googledrive_connection_id']);
			if (is_array($parameters)) {
				$cash_admin->page_data = array_merge($cash_admin->page_data,$parameters);
			}
		} else {
			$cash_admin->page_data['error_message'] = "Something went wrong. Please try again.";
		}
	}
} else if ($current_state == 'es_2' || $current_state == 'ds_2') { // post-google drive connection — asset title/description
	/***********************************************************************************************
	 * 'es_2'
	 ***********************************************************************************************/
	$cash_admin->page_data['connection_id'] = $current_settings['simple_mode_data']['googledrive_connection_id'];
	$parameters = getUploadParameters($current_settings['simple_mode_data']['googledrive_connection_id']);
	if (is_array($parameters)) {
		$cash_admin->page_data = array_merge($cash_admin->page_data,$parameters);
	}
} else if ($current_state == 'es_3' || $current_state == 'ds_3') { 
	/***********************************************************************************************
	 * 'es_3' // mailing list: connect or not       'ds_3' // paypal email address + connect
	 ***********************************************************************************************/
	if (!isset($current_settings['simple_mode_data']['asset_id'])) {
		if (isset($_POST['asset_location'])) {
			$add_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'addasset',
					'title' => $_POST['asset_title'],
					'description' => $_POST['asset_description'],
					'parent_id' => 0,
					'connection_id' => $current_settings['simple_mode_data']['googledrive_connection_id'],
					'location' => $_POST['asset_location'],
					'user_id' => $cash_admin->effective_user_id,
					'type' => 'file'
				)
			);
			if ($add_response['payload']) {
				$current_settings['simple_mode_data']['asset_id'] = $add_response['payload'];
			} else {
				$current_settings['simple_mode_data']['asset_id'] = 0;	
			}
		} else {
			$current_settings['simple_mode_data']['asset_id'] = 0;
		}

		if ($current_state == 'ds_3') {
			$flexible_price = 1;
			if (!isset($_POST['item_flexible_price'])) {
				$flexible_price = 0;
			}
			$add_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'commerce', 
					'cash_action' => 'additem',
					'user_id' => $cash_admin->effective_user_id,
					'name' => $_POST['asset_title'],
					'description' => $_POST['asset_description'],
					'price' => $_POST['item_price'],
					'flexible_price' => $flexible_price,
					'digital_fulfillment' => 1,
					'fulfillment_asset' => $current_settings['simple_mode_data']['asset_id']
				)
			);
			if ($add_response['payload']) {
				$current_settings['simple_mode_data']['item_id'] = $add_response['payload'];
			} else {
				$current_settings['simple_mode_data']['item_id'] = 0;
			}
		}

		$cash_admin->setUserSettings($current_settings);
	}

	$page_data_object = new CASHConnection($cash_admin->effective_user_id);
	if (!isset($_POST['dosettingsadd'])) {
		// no form submitted — so we want to handle the things before saving...
		$settings_types_data = $page_data_object->getConnectionTypes();

		if ($current_state == 'es_3') {
			$seed_name = $settings_types_data['com.mailchimp']['seed'];
		} else {
			$seed_name = $settings_types_data['com.paypal']['seed'];
		}
		if (!$handle_oauth_return) {
			$return_url = rtrim(CASHSystem::getCurrentURL(),'/') . '/finalize';
			// Here's a really fucked up way of calling $seed_name::getRedirectMarkup($return_url) [5.2+ compatibility]
			$cash_admin->page_data['state_markup'] = call_user_func($seed_name . '::getRedirectMarkup', $return_url);
		} else {
			// Here's a really fucked up way of calling $seed_name::handleRedirectReturn($_REQUEST) [5.2+ compatibility]
			$connections_base_uri = rtrim(str_replace($request_parameters,'',CASHSystem::getCurrentURL()),'/');
			$_REQUEST['connections_base_uri'] = $connections_base_uri;
			$_REQUEST['return_result_directly'] = true;
			$cash_admin->page_data['state_markup'] = call_user_func($seed_name . '::handleRedirectReturn', $_REQUEST);
			$cash_admin->page_data['oauth_return'] = true;
		}
	} else {
		// form has been submitted. save the mailchimp connection, move on to the next...
		$settings_name = $_POST['settings_name'];
		$settings_type = $_POST['settings_type'];
		unset($_POST['settings_name'],$_POST['settings_type'],$_POST['dosettingsadd']);
		$result = $page_data_object->setSettings(
			$settings_name,
			$settings_type,
			$_POST
		);
		if (!$result) {
			$result = getConnectionId($settings_name);
		}
		if ($result) {
			if ($current_state == 'es_3') {
				$current_settings['simple_mode_data']['mailchimp_connection_id'] = $result;
				$current_settings['simple_mode_data']['current_state'] = 'es_4';
				$cash_admin->setUserSettings($current_settings);
				$current_state = 'es_4';
			} else {
				$current_settings['simple_mode_data']['paypal_connection_id'] = $result;
				$current_settings['simple_mode_data']['current_state'] = 'ds_4';
				$cash_admin->setUserSettings($current_settings);
				$current_state = 'ds_4';
			}
		}
	}
} else if ($current_state == 'ds_5') {
	/***********************************************************************************************
	 * 'ds_5'
	 ***********************************************************************************************/
	if (isset($_POST['message_success'])) {
		$add_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addelement',
				'name' => $_POST['element_name'],
				'type' => $_POST['element_type'],
				'options_data' => array(
					'message_error' => $_POST['message_error'],
					'message_success' => $_POST['message_success'],
					'item_id' => $current_settings['simple_mode_data']['item_id'],
					'connection_id' => $current_settings['simple_mode_data']['paypal_connection_id']
				),
				'user_id' => $cash_admin->effective_user_id
			)
		);
		$element_id = $add_response['payload'];
		if ($element_id) {
			$current_settings['simple_mode_data']['main_element_id'] = $element_id;
			$cash_admin->setUserSettings($current_settings);
		}
	}
} else if ($current_state == 'es_5') {
	/***********************************************************************************************
	 * 'es_5'
	 ***********************************************************************************************/
	if (isset($_POST['message_success'])) {
		if (isset($current_settings['simple_mode_data']['mailchimp_connection_id'])) {
			$connection_id = $current_settings['simple_mode_data']['mailchimp_connection_id'];
		} else {
			$connection_id = 0;
		}
		$add_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'addlist',
				'name' => 'Created on' . date('F j'),
				'description' => 'This list was automatically set up.',
				'connection_id' => $connection_id,
				'user_id' => $cash_admin->effective_user_id
			)
		);
		$list_id = $add_response['payload'];

		$add_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addelement',
				'name' => $_POST['element_name'],
				'type' => $_POST['element_type'],
				'options_data' => array(
					'message_invalid_email' => $_POST['message_invalid_email'],
					'message_privacy' => $_POST['message_privacy'],
					'message_success' => $_POST['message_success'],
					'email_list_id' => $list_id,
					'asset_id' => $current_settings['simple_mode_data']['asset_id'],
					'comment_or_radio' => 0,
					'do_not_verify' => 1
				),
				'user_id' => $cash_admin->effective_user_id
			)
		);
		$element_id = $add_response['payload'];
		if ($element_id) {
			$current_settings['simple_mode_data']['main_element_id'] = $element_id;
			$current_settings['simple_mode_data']['es_list_id'] = $list_id;
			$cash_admin->setUserSettings($current_settings);
		}
	}
} else if ($current_state == 'es_6' || $current_state == 'ds_6') {
	/***********************************************************************************************
	 * 'es_6'
	 ***********************************************************************************************/
	if (isset($_POST['element_type'])) {
		$add_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addelement',
				'name' => $_POST['element_name'],
				'type' => $_POST['element_type'],
				'options_data' => array(
					'storedcotent' => $_POST['element_content']
				),
				'user_id' => $cash_admin->effective_user_id
			)
		);
		$element_id = $add_response['payload'];
		if ($element_id) {
			$current_settings['simple_mode_data']['staticcontent_element_id'] = $element_id;	
			$cash_admin->setUserSettings($current_settings);
		}
	}

	if (isset($current_settings['simple_mode_data']['googledrive_connection_id'])) {
		$cash_admin->page_data['connection_id'] = $current_settings['simple_mode_data']['googledrive_connection_id'];
		$parameters = getUploadParameters($current_settings['simple_mode_data']['googledrive_connection_id']);
		if (is_array($parameters)) {
			$cash_admin->page_data = array_merge($cash_admin->page_data,$parameters);
		}
	}
} else if ($current_state == 'es_final' || $current_state == 'ds_final') {
	/***********************************************************************************************
	 * 'es_final' (email signup dashboard)
	 ***********************************************************************************************/
	if (isset($_POST['design_font'])) {
		// if asset_location then create asset, make it public, use the URL
		$bg_image = '';
		if (isset($_POST['asset_location']) && $current_settings['simple_mode_data']['googledrive_connection_id']) {
			$add_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'asset', 
					'cash_action' => 'addasset',
					'title' => 'Background image',
					'description' => '',
					'parent_id' => 0,
					'connection_id' => $current_settings['simple_mode_data']['googledrive_connection_id'],
					'location' => $_POST['asset_location'],
					'user_id' => $cash_admin->effective_user_id,
					'type' => 'file'
				)
			);
			if ($add_response['payload']) {
				$success_response = $cash_admin->requestAndStore(
					array(
						'cash_request_type' => 'asset', 
						'cash_action' => 'makepublic',
						'id' => $add_response['payload']
					)
				);
				if ($success_response['payload']) {
					$bg_image = ' url(' . $success_response['payload'] . ') center top';
				}
			}
		}

		// check that color strings start with a # and are EITHER 4 or 7 characters total
		$bg_color = 'transparent';
		$text_color = '#231F20';
		$_POST['background_color'] = trim($_POST['background_color']);
		$_POST['text_color'] = trim($_POST['text_color']);
		if (substr($_POST['background_color'],0,1) == '#' && (strlen($_POST['background_color']) == 4 || strlen($_POST['background_color']) == 7)) {
			$bg_color = $_POST['background_color'];
		}
		if (substr($_POST['text_color'],0,1) == '#' && (strlen($_POST['text_color']) == 4 || strlen($_POST['text_color']) == 7)) {
			$text_color = $_POST['text_color'];
		}

		// get template, make design changes
		$template =  str_replace(
			'body {font-family:Helvetica,Arial,sans-serif;font-size:14px;line-height:1.65em;background:transparent;color:#231F20;}', 
			'body {font-family:' . $_POST['design_font'] . ';font-size:14px;line-height:1.65em;background:' . $bg_color . $bg_image . ';color:' . $text_color . ';}', 
			file_get_contents(dirname(CASH_PLATFORM_PATH) . '/settings/defaults/page.mustache')
		);

		// okay i'm a picky jerk, but seriously futura cannot be fake boldened
		if ($_POST['design_font'] == "Futura, 'Trebuchet MS', Arial, sans-serif") {
			$template =  str_replace(
				'font-weight:bold', 
				'font-weight:normal', 
				$template
			);
		}

		// now lay in the new elements
		$template =  str_replace(
			'{{{element_markup}}}', 
			'{{{element_' . $current_settings['simple_mode_data']['staticcontent_element_id'] . '}}}<br /><br />{{{element_' . $current_settings['simple_mode_data']['main_element_id'] . '}}}', 
			$template
		);

		// save the new template
		$template_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'settemplate',
				'name' => 'Generated template (from initial walkthrough)',
				'template' => $template,
				'template_id' => false,
				'user_id' => $cash_admin->effective_user_id
			)
		);
		if ($template_response['payload']) {
			// publish the template
			$settings_request = new CASHRequest(
				array(
					'cash_request_type' => 'system', 
					'cash_action' => 'setsettings',
					'type' => 'public_profile_template',
					'value' => $template_response['payload'],
					'user_id' => $cash_admin->effective_user_id
				)
			);
		}

		$cash_admin->page_data['first_success'] = true;
	} else {		
		if ($current_state == 'es_final') {
			// we need to get a count for the list
			$list_analytics = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'people', 
					'cash_action' => 'getanalytics',
					'analtyics_type' => 'listmembership',
					'list_id' => $current_settings['simple_mode_data']['es_list_id'],
					'user_id' => $cash_admin->effective_user_id
				)
			);
			$cash_admin->page_data['analytics_active'] = $list_analytics['payload']['active'];
			if ($list_analytics['payload']['active'] < 10) {
				// this is kind of dumb, but small numbers look shitty. so we'll fix that.
				$cash_admin->page_data['force_center'] = true;
			}
		} else {
			// get sales info
			$transaction_analytics = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'commerce', 
					'cash_action' => 'getanalytics',
					'analtyics_type' => 'transactions',
					'user_id' => $cash_admin->effective_user_id
				)
			);
			$cash_admin->page_data['analytics_transactions'] = $transaction_analytics['payload']['total_transactions'];
			$cash_admin->page_data['analytics_gross'] = number_format($transaction_analytics['payload']['total_gross'],2);
		}
	}

	// these bits no matter what:
	// get a username / url for the user
	$user_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'getuser',
			'user_id' => $cash_admin->effective_user_id
		)
	);
	if (is_array($user_response['payload'])) {
		$current_username = $user_response['payload']['username'];
	}
	$cash_admin->page_data['user_page_uri'] = str_replace('https','http',rtrim(str_replace('admin', $current_username, CASHSystem::getCurrentURL()),'/'));
	if (is_array($request_parameters)) {
		$param_string = implode('/', $request_parameters);
	} else {
		$param_string = '';
	}
	$cash_admin->page_data['user_page_uri'] = str_replace('/walkthrough/' . $param_string, '', $cash_admin->page_data['user_page_uri']);
	if (defined('COMPUTED_DOMAIN_IN_USER_URL') && defined('PREFERRED_DOMAIN_IN_USER_URL')) {
		$cash_admin->page_data['user_page_uri'] = str_replace(COMPUTED_DOMAIN_IN_USER_URL, PREFERRED_DOMAIN_IN_USER_URL, $cash_admin->page_data['user_page_uri']);
	}
	$cash_admin->page_data['user_page_display_uri'] = str_replace('http://','',$cash_admin->page_data['user_page_uri']);

	// for the embeds
	$cash_admin->page_data['public_url'] = CASH_PUBLIC_URL;
	$cash_admin->page_data['id'] = $current_settings['simple_mode_data']['main_element_id'];

} else if ($current_state == 'es_export') { // handles export for an email signup list
	/***********************************************************************************************
	 * 'es_export' (exporting mailing list)
	 ***********************************************************************************************/
	$list_details = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'viewlist',
			'list_id' => $current_settings['simple_mode_data']['es_list_id'],
			'user_id' => $cash_admin->effective_user_id,
			'unlimited' => true
		)
	);
	if (is_array($list_details)) {
		header('Content-Disposition: attachment; filename="list_' . $current_settings['simple_mode_data']['es_list_id'] . '_export.csv"');
		if ($list_details['status_uid'] == 'people_viewlist_200') {
			echo '"email address","display name","initial comment","additional data","verified","active","join date"' . "\n";
			foreach ($list_details['payload']['members'] as $entry) {
			    echo '"' . str_replace ('"','""',$entry['email_address']) . '"';
				echo ',"' . str_replace ('"','""',$entry['display_name']) . '"';
				echo ',"' . str_replace ('"','""',$entry['initial_comment']) . '"';
				echo ',"' . str_replace ('"','""',$entry['additional_data']) . '"';
				echo ',"' . str_replace ('"','""',$entry['verified']) . '"';
				echo ',"' . str_replace ('"','""',$entry['active']) . '"';
				echo ',"' . date('M j, Y h:iA T',$entry['creation_date']) . '"';
				echo "\n";
			}
		} else {
			echo "Error getting list.";
		}
	}
	exit;
}


/***************************************************************************************************
 *
 * GET THE FINAL TEMPLATE AND RENDER THE OUTPUT
 *
 ***************************************************************************************************/
$page_content_template = @file_get_contents($views_dir . '/' . $current_state . '.mustache');
$cash_admin->page_data['content'] = $cash_admin->mustache_groomer->render($page_content_template, $cash_admin->page_data);

?>