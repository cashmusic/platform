<?php
if (isset($_POST['dopublish'])) {
	$new_template = 0;
	if ($_POST['campaign_id'] != 0) {
		$current_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getcampaign',
				'id' => $_POST['campaign_id']
			)
		);
		$campaign = $current_response['payload'];
		if ($campaign['template_id'] != 0) {
			$new_template = $campaign['template_id'];
		} else {
			$template_default = file_get_contents(dirname(CASH_PLATFORM_PATH) . '/settings/defaults/page.mustache');
			$replacement = '';
			if (isset($_POST['element_id'])) {
				if ($_POST['element_id'] != 0) {
					$element_response = $cash_admin->requestAndStore(
						array(
							'cash_request_type' => 'element', 
							'cash_action' => 'getelement',
							'id' => $_POST['element_id']
						)
					);

					$replacement = '<!-- ' . $element_response['payload']['name'] . " -->\n\t\t{{{element_" . $_POST['element_id'] . '}}}';
				}
			}
			if (isset($_POST['pagetheme'])) {
				if ($_POST['pagetheme'] == 'light') {
					$template_default = str_replace('<body', '<body class="light"', $template_default);
				} else if ($_POST['pagetheme'] == 'dark') {
					$template_default = str_replace('<body', '<body class="dark"', $template_default);
				}
			}
			$template_default = str_replace('{{{element_n}}}',$replacement, $template_default);

			$template_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'system', 
					'cash_action' => 'settemplate',
					'name' => '',
					'type' => 'page',
					'template' => $template_default,
					'user_id' => $cash_admin->effective_user_id
				)
			);
			$template_id = $template_response['payload'];

			$edit_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'element', 
					'cash_action' => 'editcampaign',
					'id' => $_POST['campaign_id'],
					'template_id' => $template_id
				)
			);
			
			$new_template = $template_id;
		}
	}
	$settings_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'setsettings',
			'type' => 'public_profile_template',
			'value' => $new_template,
			'user_id' => $cash_admin->effective_user_id
		)
	);

	if ($settings_response['payload']) {
		if ($new_template == 0) {
			AdminHelper::formSuccess('Success. You have unpublished all campaigns.','/');
		} else {
			AdminHelper::formSuccess('Success. Campaign published.','/');
		}
	} else {
		AdminHelper::formFailure('Error. Something just didn\'t work right.','/');
	}
} else {
	$campaigns_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'getcampaignsforuser',
			'user_id' => $cash_admin->effective_user_id
		)
	);

	if (is_array($campaigns_response['payload'])) {
		$campaign_elements = array();
		foreach ($campaigns_response['payload'] as &$campaign) {
			if ($campaign['modification_date'] == 0) {
				$campaign['formatted_date'] = CASHSystem::formatTimeAgo($campaign['creation_date']);	
			} else {
				$campaign['formatted_date'] = CASHSystem::formatTimeAgo($campaign['modification_date']);
			}
			$cash_admin->page_data['template_id'] = $campaign['template_id'];
			if ($campaign['template_id'] == 0) {
				$elements_response = $cash_admin->requestAndStore(
					array(
						'cash_request_type' => 'element', 
						'cash_action' => 'getelementsforcampaign',
						'id' => $campaign['id']
					)
				);
				if (is_array($elements_response['payload'])) {
					$campaign_elements[] = array(
						'campaign' => $campaign['id'],
						'options' => new ArrayIterator($elements_response['payload'])
					);
				} 
			}
		}
		if (count($campaign_elements)) {
			$campaign_elements = new ArrayIterator($campaign_elements);
		} else {
			$campaign_elements = false;
		}
		$cash_admin->page_data['campaign_elements'] = $campaign_elements;
		$cash_admin->page_data['campaigns_for_user'] = new ArrayIterator($campaigns_response['payload']);
	} 

	$cash_admin->page_data['ui_title'] = 'Publish a campaign';
	$cash_admin->setPageContentTemplate('campaigns_publish');
}
?>