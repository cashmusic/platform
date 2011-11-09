<?php
// add unique page settings:
$page_title = 'Elements: Edit Element';
$page_tips = 'Edit the element per instructions.';

if (!$request_parameters) {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
}

$page_request = new CASHRequest(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getelement',
		'id' => $request_parameters[0]
	)
);

if ($page_request->response['status_uid'] == 'element_getelement_200') {
	
	$elements_data = AdminHelper::getElementsData();
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	
	if ($page_request->response['payload']['user_id'] == $effective_user) {
		$page_title = 'Elements: Edit “' . $page_request->response['payload']['name'] . '”';
	} else {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
}

$element_type = $page_request->response['payload']['type'];
if (@file_exists(ADMIN_BASE_PATH.'/components/elements' . '/' . $element_type . '/help.php')) {
	$page_tips = file_get_contents(ADMIN_BASE_PATH.'/components/elements' . '/' . $element_type . '/help.php');
}
?>