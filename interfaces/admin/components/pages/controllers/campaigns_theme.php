<?php
// form submit after wizard
if (isset($_POST['settheme'])) {
	$new_template = 0;
	$requested_campaign_id = $_POST['campaign_id'];
	
	if ($requested_campaign_id != 0) {
		
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
				'id' => $requested_campaign_id,
				'template_id' => $template_id
			)
		);
			
		if ($edit_response['payload']) {
			AdminHelper::formSuccess('Success. Page theme created. You can edit it at any time.','/');
		} else {
			AdminHelper::formFailure('Error. Something just didn\'t work right.','/');
		}

	}
} else {
	// you're a wizard now, harry!
	$new_template = 0;
	$requested_campaign_id = $request_parameters[0];

	$current_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'getcampaign',
			'id' => $requested_campaign_id
		)
	);
	$campaign = $current_response['payload'];
	if ($campaign['template_id'] != 0) {
		// no points for gryffindor
		AdminHelper::controllerRedirect('/elements/templates/edit/' . $campaign['template_id'] . '/page/parent/' . $requested_campaign_id);
	}

	$elements_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'getelementsforcampaign',
			'id' => $requested_campaign_id
		)
	);
			
	if ( is_array($elements_response['payload']) ) {
		$campaign_elements = new ArrayIterator($elements_response['payload']);
	} else {
		$campaign_elements = false;
	}
	$cash_admin->page_data['campaign_elements'] = $campaign_elements; 
	$cash_admin->page_data['campaign_id'] = $requested_campaign_id; 

	$cash_admin->page_data['ui_title'] = 'Create a page theme';
	$cash_admin->setPageContentTemplate('campaigns_theme');
}
?>