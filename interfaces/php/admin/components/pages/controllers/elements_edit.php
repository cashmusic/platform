<?php
if (!$request_parameters) {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
}
 
$current_element = $cash_admin->setCurrentElement($request_parameters[0]);

if ($current_element) {
	$cash_admin->page_data['form_state_action'] = 'doelementedit';	
	$elements_data = AdminHelper::getElementsData();
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	
	if ($current_element['user_id'] == $effective_user) {
		$cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getanalytics',
				'analtyics_type' => 'elementbylocation',
				'element_id' => $request_parameters[0],
				'user_id' => AdminHelper::getPersistentData('cash_effective_user')
			),
			'elementbylocation'
		);
		
		$cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getanalytics',
				'analtyics_type' => 'elementbymethod',
				'element_id' => $request_parameters[0],
				'user_id' => AdminHelper::getPersistentData('cash_effective_user')
			),
			'elementbymethod'
		);

		if (@file_exists(CASH_PLATFORM_ROOT.'/elements' . '/' . $current_element['type'] . '/admin.php')) {
			include(CASH_PLATFORM_ROOT.'/elements' . '/' . $current_element['type'] . '/admin.php');
			$cash_admin->page_data['title'] = 'Elements: “' . $current_element['name'] . '”';
			$cash_admin->page_data['element_button_text'] = 'Edit The Element';
			$element_rendered_content = $cash_admin->mustache_groomer->render(file_get_contents(CASH_PLATFORM_ROOT.'/elements' . '/' . $current_element['type'] . '/templates/admin.mustache'), $cash_admin->page_data);
		} else {
			$element_rendered_content = "Could not find the admin.php file for this .";
		}
	} else {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
}
?>