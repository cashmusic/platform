<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/elements/');
}
 
$current_element = $cash_admin->setCurrentElement($request_parameters[0]);

if ($current_element) {
	$cash_admin->page_data['form_state_action'] = 'doelementedit';	
	$cash_admin->page_data = array_merge($cash_admin->page_data,$current_element);
	$elements_data = AdminHelper::getElementsData();
	$effective_user = $cash_admin->effective_user_id;
	
	if ($current_element['user_id'] == $effective_user) {
		// handle template change
		if (isset($_POST['change_template_id'])) {
			$new_template_id = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'element', 
					'cash_action' => 'setelementtemplate',
					'element_id' => $request_parameters[0],
					'template_id' => $_POST['change_template_id']
				)
			);
			if ($new_template_id) {
				$cash_admin->page_data['template_id'] = $_POST['change_template_id'];
			}
		}

		// deal with templates 
		$embed_templates = AdminHelper::echoTemplateOptions('embed',$cash_admin->page_data['template_id']);
		if ($embed_templates) {
			$cash_admin->page_data['template_options'] = '<option value="0" selected="selected">Use default template</option>';
			$cash_admin->page_data['template_options'] .= $embed_templates;
			$cash_admin->page_data['defined_embed_templates'] = true;
			if (!$cash_admin->page_data['template_id']) {
				$cash_admin->page_data['embed_template_name'] = 'default';
			} else {
				$template_response = $cash_admin->requestAndStore(
					array(
						'cash_request_type' => 'system', 
						'cash_action' => 'gettemplate',
						'template_id' => $cash_admin->page_data['template_id'],
						'all_details' => 1,
						'user_id' => $current_element['user_id']
					)
				);
				if (is_array($template_response['payload'])) {
					$cash_admin->page_data['embed_template_name'] = '“' . $template_response['payload']['name'] . '”';
				} else {
					$cash_admin->page_data['embed_template_name'] = 'error. please choose new template.';
				}
			}
		} else {
			$cash_admin->page_data['defined_embed_templates'] = false;
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
		AdminHelper::controllerRedirect('/elements/');
	}
} else {
	AdminHelper::controllerRedirect('/elements/');
}

if ($cash_admin->platform_type == 'single') {
	$cash_admin->page_data['platform_type_single'] = true;
}
$cash_admin->page_data['platform_path'] = CASH_PLATFORM_PATH;

$cash_admin->setPageContentTemplate('elements_details');
?>