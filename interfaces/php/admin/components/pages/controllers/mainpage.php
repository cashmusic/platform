<?php
/*
// banner stuff
$settings = $cash_admin->getUserSettings();
*/

// handle template change
if (isset($_POST['change_template_id'])) {
	$settings_request = new CASHRequest(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'setsettings',
			'type' => 'public_profile_template',
			'value' => $_POST['change_template_id'],
			'user_id' => $cash_admin->effective_user_id
		)
	);
}

/*
// look for a defined template
$settings_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'system', 
		'cash_action' => 'getsettings',
		'type' => 'public_profile_template',
		'user_id' => $cash_admin->effective_user_id
	)
);
if ($settings_response['payload']) {
	$cash_admin->page_data['current_page_template'] = $settings_response['payload'];
} else {
	$cash_admin->page_data['current_page_template'] = false;
}

// deal with templates and public page
$page_templates = AdminHelper::echoTemplateOptions('page',$cash_admin->page_data['current_page_template']);
if ($page_templates) {
	$cash_admin->page_data['template_options'] = '<option value="0" selected="selected">No page published</option>';
	$cash_admin->page_data['template_options'] .= $page_templates;
	$cash_admin->page_data['defined_page_templates'] = true;
} else {
	$cash_admin->page_data['defined_page_templates'] = false;
	$cash_admin->page_data['published_page'] = false;
}
*/

// get username and any user data
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

// get news for the news feed
$session_news = AdminHelper::getActivity($current_userdata);

// now set up page variables
$cash_admin->page_data['dashboard_news'] = $session_news['cash_news_content'];
$cash_admin->page_data['dashboard_news_img'] = $session_news['cash_news_img'];
if (is_array($session_news['activity']['lists'])) {
	foreach ($session_news['activity']['lists'] as &$list_stats) {
		if ($list_stats['total'] == 1) {
			$list_stats['singular'] = true;
		} else {
			$list_stats['singular'] = false;
		}
	}
}
$cash_admin->page_data['dashboard_lists'] = $session_news['activity']['lists'];
if ($session_news['activity']['orders']) {
	$cash_admin->page_data['dashboard_orders'] = count($session_news['activity']['orders']);
	if ($cash_admin->page_data['dashboard_orders'] == 1) {
		$cash_admin->page_data['dashboard_orders_singular'] = true;
	}
} else {
	$cash_admin->page_data['dashboard_orders'] = false;
}


/*
// check to see if the user has elements defined
$elements_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getelementsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

if (is_array($elements_response['payload'])) {
	// this essentially locks us to the newest template, meaning everyone gets just
	// one page template at first. if it's there, it's live
	$template_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'getnewesttemplate',
			'all_details' => true,
			'user_id' => $cash_admin->effective_user_id
		)
	);
	if ($template_response['payload']) {
		$cash_admin->page_data['page_template'] = $template_response['payload']['id'];
	}

	foreach ($elements_response['payload'] as &$element) {
		$element['type_name'] = $element['type'];
	}

	$cash_admin->page_data['elements_found'] = true;
	$cash_admin->page_data['elements_for_user'] = new ArrayIterator($elements_response['payload']);
} else {
	// no elements found, meaning it's a newer install

	// first check if they've changed the default email as a sign of step 1:
	if (CASHSystem::getDefaultEmail() != 'CASH Music <info@cashmusic.org>') {
		$cash_admin->page_data['step1_complete'] = 'complete';
	}

	// now check for assets and/or lists as a sign of step 2:
	$asset_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'getanalytics',
			'analtyics_type' => 'recentlyadded',
			'user_id' => $cash_admin->effective_user_id
		)
	);
	if (is_array($asset_response['payload'])) {
		$cash_admin->page_data['step2_complete'] = 'complete';
	} else {
		$list_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getlistsforuser',
				'user_id' => $cash_admin->effective_user_id
			)
		);
		if (is_array($asset_response['payload'])) {
			$cash_admin->page_data['step2_complete'] = 'complete';
		}
	}
}

if ($cash_admin->platform_type == 'single') {
	$cash_admin->page_data['platform_type_single'] = true;
}
*/

$cash_admin->page_data['user_page_uri'] = str_replace('https','http',rtrim(str_replace('admin', $current_username, CASHSystem::getCurrentURL()),'/'));
if (defined('COMPUTED_DOMAIN_IN_USER_URL') && defined('PREFERRED_DOMAIN_IN_USER_URL')) {
	$cash_admin->page_data['user_page_uri'] = str_replace(COMPUTED_DOMAIN_IN_USER_URL, PREFERRED_DOMAIN_IN_USER_URL, $cash_admin->page_data['user_page_uri']);
}
$cash_admin->page_data['user_page_display_uri'] = str_replace('http://','',$cash_admin->page_data['user_page_uri']);


$campaigns_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getcampaignsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

if (is_array($campaigns_response['payload'])) {
	foreach ($campaigns_response['payload'] as &$campaign) {
		if ($campaign['modification_date'] == 0) {
			$campaign['formatted_date'] = CASHSystem::formatTimeAgo($campaign['creation_date']);	
		} else {
			$campaign['formatted_date'] = CASHSystem::formatTimeAgo($campaign['modification_date']);
		}
	}
	$cash_admin->page_data['campaigns_for_user'] = new ArrayIterator($campaigns_response['payload']);
} 



$cash_admin->setPageContentTemplate('mainpage');
?>