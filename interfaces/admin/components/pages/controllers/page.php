<?php
/*******************************************************************************
 *
 * 1. GET PRIMARY/PUBLISHED PAGE SETTINGS, SET VARIABLES
 *
 ******************************************************************************/
$settings_request = new CASHRequest(
	array(
		'cash_request_type' => 'system',
		'cash_action' => 'getsettings',
		'type' => 'public_profile_template',
		'user_id' => $cash_admin->effective_user_id
	)
);
$cash_admin->page_data['public_template_id'] = $settings_request->response['payload'];

$settings_request = new CASHRequest(
	array(
		'cash_request_type' => 'system',
		'cash_action' => 'getsettings',
		'type' => 'primary_template_id',
		'user_id' => $cash_admin->effective_user_id
	)
);
$cash_admin->page_data['primary_template_id'] = $settings_request->response['payload'];

if ($cash_admin->page_data['public_template_id']) {
	// page is public, yay!
	$cash_admin->page_data['show_published'] = true;
	if (!$cash_admin->page_data['primary_template_id']) {
		// TODO: LEGACY SUPPORT AS OF MARCH 8, 2016 -- REMOVE THIS BLOCK IN NINE MONTHS +
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'setsettings',
				'type' => 'primary_template_id',
				'value' => $cash_admin->page_data['public_template_id'],
				'user_id' => $cash_admin->effective_user_id
			)
		);
		$cash_admin->page_data['primary_template_id'] = $cash_admin->page_data['public_template_id'];
	}
}
if (!$cash_admin->page_data['primary_template_id'] && !$cash_admin->page_data['public_template_id']) {
	// never has been set up ever
	$cash_admin->page_data['first_time_page'] = true;
}
if ($cash_admin->page_data['primary_template_id']) {
	// show that edit button
	$cash_admin->page_data['show_edit_button'] = true;
}


/*******************************************************************************
 *
 * 2. HANDLE PUBLISH / UNPUBLISH
 *
 ******************************************************************************/
if ($request_parameters) {
	$action = $request_parameters[0];
}

if ($action == 'publish') {
	$action_id = $cash_admin->page_data['primary_template_id'];
	$cash_admin->page_data['show_published'] = true;
}

if ($action == 'unpublish') {
	$action_id = 0;
	$cash_admin->page_data['show_published'] = false;
}

if ($action == 'publish' || $action == 'unpublish') {
	$settings_request = new CASHRequest(
		array(
			'cash_request_type' => 'system',
			'cash_action' => 'setsettings',
			'type' => 'public_profile_template',
			'value' => $action_id,
			'user_id' => $cash_admin->effective_user_id
		)
	);
}


/*******************************************************************************
 *
 * 3. GET USERNAME / URL / DATA JUNK
 *
 ******************************************************************************/
$user_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people',
		'cash_action' => 'getuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
if (is_array($user_response['payload'])) {
	$current_username = $user_response['payload']['username'];
	$current_userdata = $user_response['payload']['data'];
}

// get page url
if (SUBDOMAIN_USERNAMES) {
	//$cash_admin->page_data['user_page_uri'] = str_replace('https','http',rtrim(str_replace('admin', '', CASH_ADMIN_URL),'/'));
	$cash_admin->page_data['user_page_uri'] = str_replace('://','://' . $current_username . '.',$cash_admin->page_data['user_page_uri']);
} else {
	$cash_admin->page_data['user_page_uri'] = rtrim(str_replace('admin', $current_username, CASH_ADMIN_URL),'/');
}
$cash_admin->page_data['user_page_display_uri'] = str_replace(array('http://','https://'),'',$cash_admin->page_data['user_page_uri']);

//get public URL
$cash_admin->page_data['public_url'] = CASH_PUBLIC_URL;


/*******************************************************************************
 *
 * 4. SET THE TEMPLATE AND GO!
 *
 ******************************************************************************/
$cash_admin->setPageContentTemplate('page');
?>
