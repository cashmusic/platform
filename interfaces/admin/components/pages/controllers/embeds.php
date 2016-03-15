<?php
/*******************************************************************************
 *
 * 1. HANDLE CAMPAIGN SELECTION / DRAW SELECTOR
 *
 ******************************************************************************/
$current_campaign = $admin_primary_cash_request->sessionGet('current_campaign');
if ($current_campaign !== false) {
	$settings_request = new CASHRequest(
		array(
			'cash_request_type' => 'system',
			'cash_action' => 'getsettings',
			'type' => 'selected_campaign',
			'user_id' => $cash_admin->effective_user_id
		)
	);
	if ($settings_request->response['payload']) {
		$current_campaign = $settings_request->response['payload'];
		$admin_primary_cash_request->sessionSet('current_campaign',$current_campaign);
	}
}
if (isset($_POST['current-campaign'])) {
	$current_campaign = $_POST['current-campaign'];
	$admin_primary_cash_request->sessionSet('current_campaign',$current_campaign);
	$settings_request = new CASHRequest(
		array(
			'cash_request_type' => 'system',
			'cash_action' => 'setsettings',
			'type' => 'selected_campaign',
			'value' => $current_campaign,
			'user_id' => $cash_admin->effective_user_id
		)
	);
}
if (!$current_campaign) {
	$current_campaign = -1;
}
$cash_admin->page_data['selected_campaign']	= $current_campaign;


/*******************************************************************************
 *
 * 2. PULL USERNAME, BASIC INFORMATION
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

//get public URL
$cash_admin->page_data['public_url'] = CASH_PUBLIC_URL;

// get all campaigns
$campaigns_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element',
		'cash_action' => 'getcampaignsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

$total_campaigns = count($campaigns_response['payload']);
// set all campaigns as a mustache var
if ($campaigns_response['payload']) {
	$cash_admin->page_data['campaigns_for_user'] = new ArrayIterator($campaigns_response['payload']);
}

$all_elements_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element',
		'cash_action' => 'getelementsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
if (!is_array($all_elements_response['payload'])) {
	$all_elements_response['payload'] = array();
}
$total_elements = count($all_elements_response['payload']);


// get page url
if (SUBDOMAIN_USERNAMES) {
	$cash_admin->page_data['user_page_uri'] = rtrim(str_replace('admin', '', CASH_ADMIN_URL),'/');
	$cash_admin->page_data['user_page_uri'] = str_replace('://','://' . $current_username . '.',$cash_admin->page_data['user_page_uri']);
} else {
	$cash_admin->page_data['user_page_uri'] = rtrim(str_replace('admin', $current_username, CASH_ADMIN_URL),'/');
}
$cash_admin->page_data['user_page_display_uri'] = str_replace(array('http://','https://'),'',$cash_admin->page_data['user_page_uri']);

//get public URL
$cash_admin->page_data['public_url'] = CASH_PUBLIC_URL;

$campaign_elements = array();
$elements_for_campaign = array();
if (is_array($campaigns_response['payload'])) {
	$cash_admin->page_data['campaigns_as_options'] = '';
	foreach ($campaigns_response['payload'] as &$campaign) {
		// pull out element details
		$campaign['elements'] = json_decode($campaign['elements'],true);
		if (is_array($campaign['elements'])) {
			$campaign_elements = array_merge($campaign['elements'],$campaign_elements);
			if ($campaign['id'] == $current_campaign) {
				$elements_response = $cash_admin->requestAndStore(
					array(
						'cash_request_type' => 'element',
						'cash_action' => 'getelementsforcampaign',
						'id' => $campaign['id']
					)
				);
			}
		}
		// add campaign to dropdown options
		$cash_admin->page_data['campaigns_as_options'] .= '<option value="' . $campaign['id'] .'"';
		if ($campaign['id'] == $current_campaign) {
			$cash_admin->page_data['campaigns_as_options'] .= ' selected="selected"';
			// set the campaign as the selected campaign
			$cash_admin->page_data['element_count'] = count($campaign['elements']);
		}
		$cash_admin->page_data['campaigns_as_options'] .= '>' . $campaign['title'] . '</option>';
	}
}

if ($current_campaign == -1) {
	// show "No campaign" elements
	$extra_elements = count($all_elements_response['payload']) - count($campaign_elements);
	$cash_admin->page_data['element_count'] = $extra_elements;

	if ($extra_elements > 0) {
		$elements_for_campaign = array();
		foreach ($all_elements_response['payload'] as $element) {
			if (!in_array($element['id'], $campaign_elements)) {
				$elements_for_campaign[] = $element;
			}
		}
	}
}

// newest first
if (is_array($elements_response['payload'])) {
	$elements_for_campaign = array_reverse($elements_response['payload']);
}
foreach ($elements_for_campaign as &$element) {
	if ($element['modification_date'] == 0) {
		$element['formatted_date'] = CASHSystem::formatTimeAgo($element['creation_date']);
	} else {
		$element['formatted_date'] = CASHSystem::formatTimeAgo($element['modification_date']);
	}
}
if ($elements_for_campaign) {
	$cash_admin->page_data['elements_for_campaign'] = new ArrayIterator($elements_for_campaign);
}

if ($cash_admin->page_data['element_count'] > 0) {
	$cash_admin->page_data['has_elements'] = true;
}


if ($total_campaigns) {
	$cash_admin->page_data['has_campaigns'] = true;
	if (!$total_elements) {
		$cash_admin->page_data['campaigns_noelements'] = true;
	}
}


/*******************************************************************************
 *
 * X. SET THE TEMPLATE AND GO!
 *
 ******************************************************************************/
$cash_admin->setPageContentTemplate('embeds');
?>
