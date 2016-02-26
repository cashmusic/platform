<?php
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

// get the current template:
$settings_request = new CASHRequest(
	array(
		'cash_request_type' => 'system',
		'cash_action' => 'getsettings',
		'type' => 'public_profile_template',
		'user_id' => $cash_admin->effective_user_id
	)
);

$cash_admin->page_data['template_id'] = $settings_request->response['payload'];
if ($cash_admin->page_data['template_id']) {
	$cash_admin->page_data['show_published'] = true;
}

// handle campaign selection
$current_campaign = $admin_primary_cash_request->sessionGet('current_campaign');
if (!$current_campaign) {
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


// get page url
if (SUBDOMAIN_USERNAMES) {
	$cash_admin->page_data['user_page_uri'] = str_replace('https','http',rtrim(str_replace('admin', '', CASH_ADMIN_URL),'/'));
	$cash_admin->page_data['user_page_uri'] = str_replace('://','://' . $current_username . '.',$cash_admin->page_data['user_page_uri']);
} else {
	$cash_admin->page_data['user_page_uri'] = str_replace('https','http',rtrim(str_replace('admin', $current_username, CASH_ADMIN_URL),'/'));
}
$cash_admin->page_data['user_page_display_uri'] = str_replace('http://','',$cash_admin->page_data['user_page_uri']);


//get public URL
$cash_admin->page_data['public_url'] = CASH_PUBLIC_URL;

// all user elements defined
$elements_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element',
		'cash_action' => 'getelementsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
if (!is_array($elements_response['payload'])) {
	$elements_response['payload'] = array();
}



// get all campaigns
$campaigns_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element',
		'cash_action' => 'getcampaignsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

$total_campaigns = count($campaigns_response['payload']);
$total_elements = count($elements_response['payload']);

if ($total_campaigns) {
	//
	//
	// TODO: proper selection of elements instead of just the first one because whatever
	if (!$current_campaign) {
		$current_campaign = $campaigns_response['payload'][count($campaigns_response['payload']) - 1]['id'];
		$admin_primary_cash_request->sessionSet('current_campaign',$current_campaign);
	}

	$campaign_elements = array();
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

					if (is_array($elements_response['payload'])) {
						$elements_response['payload'] = array_reverse($elements_response['payload']);
						foreach ($elements_response['payload'] as &$element) {
							if ($element['modification_date'] == 0) {
								$element['formatted_date'] = CASHSystem::formatTimeAgo($element['creation_date']);
							} else {
								$element['formatted_date'] = CASHSystem::formatTimeAgo($element['modification_date']);
							}
						}
						$cash_admin->page_data['elements_for_campaign'] = new ArrayIterator($elements_response['payload']);

						if ($cash_admin->page_data['elements_for_campaign']){
							$cash_admin->page_data['has_elements'] = true;
						};
					}
				}
			}
			// set element count
			$campaign['element_count'] = count($campaign['elements']);

			if ($campaign['template_id'] == 0) {
				$campaign['show_wizard'] = true;
			}

			// add campaign to dropdown options
			$cash_admin->page_data['campaigns_as_options'] .= '<option value="' . $campaign['id'] .'"';
			if ($campaign['id'] == $current_campaign) {
				$cash_admin->page_data['campaigns_as_options'] .= ' selected="selected"';
			}
			$cash_admin->page_data['campaigns_as_options'] .= '>' . $campaign['title'] . '</option>';



			if ($campaign['id'] == $current_campaign) {
				// get campaign analytics
				$analytics_response = $cash_admin->requestAndStore(
					array(
						'cash_request_type' => 'element',
						'cash_action' => 'getanalyticsforcampaign',
						'id' => $campaign['id']
					)
				);
				$campaign['formatted_views'] = CASHSystem::formatCount(0 + $analytics_response['payload']['total_views']);

				// set the campaign as the selected campaign
				$cash_admin->page_data['selected_campaign']	= $campaign;
			}
		}
	}

	if ($cash_admin->page_data['template_id']) {
		foreach ($campaigns_response['payload'] as &$campaign) {
			if ($campaign['template_id'] == $cash_admin->page_data['template_id']) {
				$campaign['currently_published'] = true;
			}
		}
	}

	// set all campaigns as a mustache var
	if ($campaigns_response['payload']) {
		$cash_admin->page_data['campaigns_for_user'] = new ArrayIterator($campaigns_response['payload']);
	}
}


// figure out and select 	the correct view
$cash_admin->setPageContentTemplate('yourpage');
if ($total_campaigns) {
	$cash_admin->page_data['has_campaigns'] = true;
	if (!$total_elements) {
		$cash_admin->page_data['campaigns_noelements'] = true;
	}
} else {
	if ($total_elements) {
		$cash_admin->page_data['migrated'] = true;
	}
}
?>
