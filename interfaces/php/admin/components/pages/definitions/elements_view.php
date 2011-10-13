<?php
// add unique page settings:
$page_title = 'Elements: View All Elements';
$page_tips = 'This page lists all your defined elements. Click any of them to see embed details, make edits, or delete them.';


$elements_data = AdminHelper::getElementsData();
$effective_user = AdminHelper::getPersistentData('cash_effective_user');

if ($request_parameters) {
	$page_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'getelement',
			'element_id' => $request_parameters[0]
		)
	);
	if ($page_request->response['payload']['user_id'] == $effective_user) {
		$page_title = 'Elements: View “' . $page_request->response['payload']['name'] . '”';
	} else {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
	}
} else {
	$page_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'getelementsforuser',
			'user_id' => AdminHelper::getPersistentData('cash_effective_user')
		)
	);
}
?>