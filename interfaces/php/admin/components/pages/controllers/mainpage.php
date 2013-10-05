<?php
$cash_admin->page_data['ui_title'] = 'CASH Music: Main Page';

// banner stuff
$settings = $cash_admin->getUserSettings();

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
$tumblr_seed = new TumblrSeed();
$tumblr_request = $tumblr_seed->getTumblrFeed('blog.cashmusic.org',0,'platformnews');
//error_log(print_r($tumblr_request,true));

$cash_admin->page_data['dashboard_news_img'] = null;
$cash_admin->page_data['dashboard_news'] = "<p>News could not be read. So let's say no news is good news.</p>";
$doc = new DOMDocument();
@$doc->loadHTML($tumblr_request[0]->{'regular-body'});
$imgs = $doc->getElementsByTagName('img');
if ($imgs->length) {
	$cash_admin->page_data['dashboard_news_img'] = $imgs->item(0)->getAttribute('src');
}
$ps = $doc->getElementsByTagName('p');
foreach ($ps as $p) {
	if ($p->nodeValue) {
		$cash_admin->page_data['dashboard_news'] = '<p><b><i>' . $tumblr_request[0]->{$tumblr_request[0]->type . '-title'} . ':</i></b> ' . 
			$p->nodeValue . ' <a href="' . $tumblr_request[0]->{'url-with-slug'} . '" class="usecolor1" target="_blank">' . 'Read more.</a></p>';
		break;
	}
}

// check to see if the user has elements defined
$elements_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getelementsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
$elements_data = AdminHelper::getElementsData();

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
		if (array_key_exists($element['type'],$elements_data)) {
			$element['type_name'] = $elements_data[$element['type']]['name'];
		}
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

// menu hack (we want to display in-page menus outside the normal structure on the main page)
$cash_admin->page_data['section_menu'] = '<ul class="pagebasemenu"><li><a href="#activity"><i class="icon icon-bolt"></i> News / activity</a></li><li><a href="#elements"><i class="icon icon-puzzle-piece"></i> Elements</a></li><li><a href="#publish"><i class="icon icon-list-alt"></i> Publish / embed</a></li></ul>';

$cash_admin->setPageContentTemplate('mainpage');
?>