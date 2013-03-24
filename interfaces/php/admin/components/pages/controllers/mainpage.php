<?php
$cash_admin->page_data['ui_title'] = 'CASH Music: Main Page';

// banner stuff
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	$cash_admin->page_data['banner_main_content'] = '<a href="' . ADMIN_WWW_BASE_PATH . '/assets/" class="usecolor1">Assets</a>, your songs, photos, cover art, etc. <a href="' 
		. ADMIN_WWW_BASE_PATH . '/people/" class="usecolor2">People</a>, fans, mailing lists, anyone you need to connect with on a regular basis. <a href="' 
		. ADMIN_WWW_BASE_PATH . '/commerce/" class="usecolor3">Commerce</a> is where you’ll find info on all your orders. And <a href="' 
		. ADMIN_WWW_BASE_PATH . '/calendar/" class="usecolor4">Calendar</a>, keeps a record of all your shows in one place.<br /><br />'
		. 'The last main category is <a href="' . ADMIN_WWW_BASE_PATH . '/elements/" class="usecolor5">Elements</a>, where Assets, People, Commerce, and Calendar can be combined to make customized tools for your site. Things like email collection, digital sales, and social feeds all just a copy/paste away.<br /><br />'
		. '<div class="moreinfospc">&nbsp;</div></div>';
}

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

// get news for the news feed
$dashboard_news_response = CASHSystem::getURLContents('http://cashmusic.s3.amazonaws.com/permalink/admin/mainpage.html');
if ($dashboard_news_response) {
	$cash_admin->page_data['dashboard_news'] = '<h3>News</h3>' . $dashboard_news_response;
} else {
	$cash_admin->page_data['dashboard_news'] = '<h3 class="fadedtext">Sorry</h3><p class="fadedtext">Couldn\'t get news from the CASH servers.</p>';
}

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

	$cash_admin->page_data['elements_for_user'] = true;
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
if (defined('COMPUTED_DOMAIN_IN_USER_URL') && defined('PREFERRED_DOMAIN_IN_USER_URL')) {
	$cash_admin->page_data['user_page_uri'] = str_replace(COMPUTED_DOMAIN_IN_USER_URL, PREFERRED_DOMAIN_IN_USER_URL, $cash_admin->page_data['user_page_uri']);
}
$cash_admin->page_data['user_page_display_uri'] = str_replace('http://','',$cash_admin->page_data['user_page_uri']);

if ($cash_admin->platform_type == 'single') {
	$cash_admin->page_data['platform_type_single'] = true;
}

$cash_admin->setPageContentTemplate('mainpage');
?>