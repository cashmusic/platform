<?php
if (isset($request_parameters[0])) {
	$effective_user = $cash_admin->effective_user_id;
	$template_id = $request_parameters[0];
	if ($request_parameters[0] == 0 && isset($request_parameters[1]) && isset($request_parameters[2]) && isset($request_parameters[3])) {
		// need to add a new template. GET ON IT. JAM ON IT.
		//
		// weird setup, but we want the edit page to work seamlessly even when there is no element...
		$template_default = file_get_contents(dirname(CASH_PLATFORM_PATH) . '/settings/defaults/' . $request_parameters[1] . '.mustache');
		if ($request_parameters[1] == 'page') {
			$template_default = str_replace('{{{element_n}}}','<!--{{{element_n}}}-->',$template_default);
		}
		$template_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'settemplate',
				'name' => '',
				'type' => $request_parameters[1],
				'template' => $template_default,
				'user_id' => $effective_user
			)
		);

		if ($request_parameters[1] == 'embed') {
			$cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'element', 
					'cash_action' => 'setelementtemplate',
					'element_id' => $request_parameters[3],
					'template_id' => $template_response['payload']
				)
			);
		} else if ($request_parameters[1] == 'page') {
			$edit_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'element', 
					'cash_action' => 'editcampaign',
					'id' => $request_parameters[3],
					'template_id' => $template_response['payload']
				)
			);
		}

		$template_id = $template_response['payload'];
	}

	if ($request_parameters[1] == 'page') {
		$current_campaign = $admin_primary_cash_request->sessionGet('current_campaign');
			$elements_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'element', 
					'cash_action' => 'getelementsforcampaign',
					'id' => $current_campaign
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
			}
	}

	$template_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'gettemplate',
			'template_id' => $template_id,
			'all_details' => 1,
			'user_id' => $effective_user
		)
	);
	if (is_array($template_response['payload'])) {
		$cash_admin->page_data = array_merge($template_response['payload'],$cash_admin->page_data);
		if ($template_response['payload']['type'] == 'embed') {
			$cash_admin->page_data['is_embed'] = true;
		}
	}
}

$cash_admin->page_data['button_text'] = 'Save changes';

$cash_admin->setPageContentTemplate('elements_template_details');
?>