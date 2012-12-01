<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/elements/view/');
}
 
$current_element = $cash_admin->setCurrentElement($request_parameters[0]);

if ($current_element) {
	$cash_admin->page_data['form_state_action'] = 'doelementedit';	
	$cash_admin->page_data = array_merge($cash_admin->page_data,$current_element);
	$elements_data = AdminHelper::getElementsData();
	$effective_user = $cash_admin->effective_user_id;
	
	if ($current_element['user_id'] == $effective_user) {
		$location_analytics = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getanalytics',
				'analtyics_type' => 'elementbylocation',
				'element_id' => $request_parameters[0],
				'user_id' => $cash_admin->effective_user_id
			)
		);
		$cash_admin->page_data['total_views'] = 0;
		if (is_array($location_analytics['payload'])) {
			foreach ($location_analytics['payload'] as $entry) {
				$cash_admin->page_data['total_views'] += $entry['total'];
			}
			$cash_admin->page_data['location_analytics'] = new ArrayIterator($location_analytics['payload']);
		}
		
		$method_analytics = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getanalytics',
				'analtyics_type' => 'elementbymethod',
				'element_id' => $request_parameters[0],
				'user_id' => $cash_admin->effective_user_id
			)
		);
		if (is_array($method_analytics['payload'])) {
			foreach ($method_analytics['payload'] as &$entry) {
				$methods_string = array ('direct','api_public','api_key','api_fullauth');
				$methods_translation = array('direct (embedded on this site)','api_public (shared to another site)','api_key (shared to another site)','api_fullauth (another site with your API credentials)');
				$entry['access_method'] = str_replace($methods_string,$methods_translation,$entry['access_method']);
			}
			$cash_admin->page_data['method_analytics'] = new ArrayIterator($method_analytics['payload']);
		}

		if (@file_exists(CASH_PLATFORM_ROOT.'/elements' . '/' . $current_element['type'] . '/admin.php')) {
			include(CASH_PLATFORM_ROOT.'/elements' . '/' . $current_element['type'] . '/admin.php');
			$cash_admin->page_data['ui_title'] = 'Elements: “' . $current_element['name'] . '”';
			$cash_admin->page_data['public_url'] = CASH_PUBLIC_URL;
			$cash_admin->page_data['element_button_text'] = 'Edit the element';
			$cash_admin->page_data['element_rendered_content'] = $cash_admin->mustache_groomer->render(file_get_contents(CASH_PLATFORM_ROOT.'/elements' . '/' . $current_element['type'] . '/templates/admin.mustache'), $cash_admin->page_data);
		} else {
			$cash_admin->page_data['element_rendered_content'] = "Could not find the admin.php file for this .";
		}
	} else {
		AdminHelper::controllerRedirect('/elements/view/');
	}
} else {
	AdminHelper::controllerRedirect('/elements/view/');
}

if ($cash_admin->platform_type == 'single') {
	$cash_admin->page_data['platform_type_single'] = true;
}
$cash_admin->page_data['platform_path'] = CASH_PLATFORM_PATH;

$cash_admin->setPageContentTemplate('elements_details');
?>