<?php
// add unique page settings:
$page_title = 'Elements: Edit Element';
$page_tips = 'Edit the element per instructions.';

if (!$request_parameters) {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
}

$page_request = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getelement',
		'id' => $request_parameters[0]
	),
	'originalelement'
);

if ($page_request['status_uid'] == 'element_getelement_200') {
	
	$elements_data = AdminHelper::getElementsData();
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	
	if ($page_request['payload']['user_id'] == $effective_user) {
		$page_title = 'Elements: Edit “' . $page_request['payload']['name'] . '”';
	} else {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
}

$element_type = $page_request['payload']['type'];
if (@file_exists(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_type . '/help.php')) {
	$page_tips = file_get_contents(CASH_PLATFORM_ROOT.'/elements' . '/' . $element_type . '/help.php');
}
?>