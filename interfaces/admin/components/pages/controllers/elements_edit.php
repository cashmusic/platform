<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/');
}

$current_element = $cash_admin->setCurrentElement($request_parameters[0]);

if ($current_element) {
	$cash_admin->page_data['form_state_action'] = 'doelementedit';
	$cash_admin->page_data = array_merge($cash_admin->page_data,$current_element);
	$effective_user = $cash_admin->effective_user_id;

	if ($current_element['user_id'] == $effective_user) {
		// handle template change
		if (isset($_POST['change_template_id'])) {
			if ($_POST['change_template_id'] != $_POST['current_template_id']) {
				$new_template_id = $cash_admin->requestAndStore(
					array(
						'cash_request_type' => 'element',
						'cash_action' => 'setelementtemplate',
						'element_id' => $request_parameters[0],
						'template_id' => $_POST['change_template_id']
					)
				);
				if ($new_template_id) {
					if ($_POST['current_template_id'] > 0) {
						// delete old custom templates
						$cash_admin->requestAndStore(
							array(
								'cash_request_type' => 'system',
								'cash_action' => 'deletetemplate',
								'template_id' => $_POST['current_template_id']
							)
						);
					}
					$cash_admin->page_data['template_id'] = $_POST['change_template_id'];
				}
			}
		}

		// deal with templates
		$embed_templates = AdminHelper::echoTemplateOptions('embed',$cash_admin->page_data['template_id']);
		$cash_admin->page_data['template_options'] = $embed_templates;

		if ($cash_admin->page_data['template_id'] >= 0) {
			$cash_admin->page_data['custom_template'] = true;
		}

		$analytics = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element',
				'cash_action' => 'getanalytics',
				'analtyics_type' => 'elementbasics',
				'element_id' => $request_parameters[0],
				'user_id' => $cash_admin->effective_user_id
			)
		);
		$cash_admin->page_data['total_views'] = 0;
		if (is_array($analytics['payload'])) {
			$cash_admin->page_data['total_views'] = CASHSystem::formatCount($analytics['payload']['total']);

			$methods_array   = array();
			$locations_array = array();

			foreach ($analytics['payload']['methods'] as $method => $total) {
				$methods_string = array ('direct','api_public','api_key','api_fullauth');
				$methods_translation = array('direct (embedded on this site)','api_public (shared to another site)','api_key (shared to another site)','api_fullauth (another site with your API credentials)');
				$methods_array[] = array(
					'access_method' => str_replace($methods_string,$methods_translation,$method),
					'total' => $total
				);
			}
			foreach ($analytics['payload']['locations'] as $location => $total) {
				$locations_array[] = array(
					'access_location' => $location,
					'total' => $total
				);
			}

			$tmp_locations_array = array(); // temp array to combine totals by hostname
			foreach ($locations_array as $key => $location) {
				// cycle through all locations, push to temp array and combine if necessary
				$parsed = parse_url($location['access_location']);
				if (isset($tmp_locations_array[$parsed['host']])) {
					$tmp_locations_array[$parsed['host']] = $tmp_locations_array[$parsed['host']] + $location['total'];
				} else {
					$tmp_locations_array[$parsed['host']] = $location['total'];
				}
			}
			arsort($tmp_locations_array); // sort temp array most to least
			$tmp_locations_array = array_slice($tmp_locations_array, 0, 5); // trim temp array to no more than 5

			$locations_array = array(); // let's rebuild the locations array
			foreach ($tmp_locations_array as $location => $total) {
				$locations_array[] = array(
					'access_location' => $location,
					'total' => $total
				);
			}

			$cash_admin->page_data['location_analytics'] = new ArrayIterator($locations_array);
			$cash_admin->page_data['method_analytics'] = new ArrayIterator($methods_array);
		}

		// Detects if element add has happened and deals with POST data if it has
		AdminHelper::handleElementFormPOST($_POST,$cash_admin);

		// Set basic id/name stuff for the element
		AdminHelper::setBasicElementFormData($cash_admin);

		// pull stored element data
		$cash_admin->page_data = array_merge($cash_admin->page_data,AdminHelper::getElementValues($current_element));

		$cash_admin->page_data['ui_title'] = '' . $current_element['name'] . '';
		$cash_admin->page_data['element_button_text'] = 'Edit the element';
		$cash_admin->page_data['element_rendered_content'] = $cash_admin->mustache_groomer->render(AdminHelper::getElementTemplate($current_element), $cash_admin->page_data);

		$campaign_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element',
				'cash_action' => 'getcampaignforelement',
				'id' => $current_element['id']
			)
		);
		if ($campaign_response['payload']) {
			$cash_admin->page_data['campaign_id'] = $campaign_response['payload']['id'];
			$cash_admin->page_data['campaign_title'] = $campaign_response['payload']['title'];
		}
	} else {
		AdminHelper::controllerRedirect('/elements/');
	}
} else {
	AdminHelper::controllerRedirect('/elements/');
}

$cash_admin->page_data['platform_path'] = CASH_PLATFORM_PATH;

$cash_admin->setPageContentTemplate('elements_details');
?>
