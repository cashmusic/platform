<?php
// add unique page settings:
$page_title = 'Elements: Your Elements';
$page_tips = 'This page lists all your defined elements. Click any of them to see embed details, make edits, or delete them.';


$elements_data = AdminHelper::getElementsData();
$effective_user = AdminHelper::getPersistentData('cash_effective_user');

if ($request_parameters) {
	$page_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'getelement',
			'id' => $request_parameters[0]
		)
	);
	if ($page_request->response['payload']['user_id'] == $effective_user) {
		$page_title = 'Elements: View “' . $page_request->response['payload']['name'] . '”';
		
		$element_type = $page_request->response['payload']['type'];
		if (@file_exists(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_type . '/help.php')) {
			$page_tips = file_get_contents(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_type . '/help.php');
		}
		
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
	} else {
		// var_dump($page_request->response);
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
	}
} else {
	$cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'getelementsforuser',
			'user_id' => AdminHelper::getPersistentData('cash_effective_user')
		),
		'getelementsforuser'
	);
}
?>