<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/');
}
 
$current_element = $cash_admin->setCurrentElement($request_parameters[0]);


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
			$cash_admin->page_data['total_views'] = $analytics['payload']['total'];

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

			$cash_admin->page_data['location_analytics'] = new ArrayIterator($locations_array);
			$cash_admin->page_data['method_analytics'] = new ArrayIterator($methods_array);
		}


		// Set basic id/name stuff for the element
		//AdminHelper::setBasicElementFormData($cash_admin);

		/*$app_json = AdminHelper::getElementAppJSON($current_element['type']);
		if ($app_json) {
			foreach ($app_json['options'] as $section_name => $details) {
				foreach ($details['data'] as $data => $values) {
					// 95% of the time all options will be set, but we check in case NEW options
					// have been added to the app.json definition since this element was first added
					if (isset($current_element['options'][$data])) {
						if ($values['type'] == 'select') {
							$default_val = AdminHelper::echoFormOptions(str_replace('/','_',$values['values']),$current_element['options'][$data],false,true);
						} else {
							$default_val = $current_element['options'][$data];
						}
					} else {
						// option not defined, so instead spit out defaults
						if (isset($values['default']) && $values['type'] !== 'select') {
							if ($values['type'] == 'boolean') {
								if ($values['default']) {
									$default_val = true;
								}
							} else if ($values['type'] == 'number') {
								$default_val = $values['default'];
							} else {
								$default_val = $values['default']['en'];
							}
						}
						if ($values['type'] == 'select') {
							$default_val = AdminHelper::echoFormOptions(str_replace('/','_',$values['values']),0,false,true);
						}
					}
					$cash_admin->page_data['options_' . $data] = $default_val;
				}
			}
			$cash_admin->page_data['ui_title'] = '' . $current_element['name'] . '';
			$cash_admin->page_data['public_url'] = CASH_PUBLIC_URL;
			$cash_admin->page_data['element_button_text'] = 'Edit the element';
			$cash_admin->page_data['element_rendered_content'] = $cash_admin->mustache_groomer->render(AdminHelper::getElementTemplate($current_element['type']), $cash_admin->page_data);
		}*/

		$cash_admin->page_data['ui_title'] = '' . $current_element['name'] . '';

		/*$campaign_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getcampaignforelement',
				'id' => $current_element['id']
			)
		);*/
		
if ($cash_admin->platform_type == 'single') {
	$cash_admin->page_data['platform_type_single'] = true;
}
$cash_admin->page_data['platform_path'] = CASH_PLATFORM_PATH;

$cash_admin->setPageContentTemplate('elements_stats');
?>