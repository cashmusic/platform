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

$cash_admin->page_data['ui_title'] = '' . $current_element['name'] . '';
$cash_admin->page_data['id'] = $current_element['id'];
		
if ($cash_admin->platform_type == 'single') {
	$cash_admin->page_data['platform_type_single'] = true;
}
$cash_admin->page_data['platform_path'] = CASH_PLATFORM_PATH;

$cash_admin->setPageContentTemplate('elements_stats');
?>