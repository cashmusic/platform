<?php
// add unique page settings:
$page_title = 'Elements: Delete Element';
$page_tips = 'Confirm to delete.';

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
		if (isset($_POST['doelementdelete']) || isset($_GET['modalconfirm'])) {
			$element_delete_request = new CASHRequest(
				array(
					'cash_request_type' => 'element', 
					'cash_action' => 'deleteelement',
					'id' => $request_parameters[0]
				)
			);
			if ($element_delete_request->response['status_uid'] == 'element_deleteelement_200') {
				header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
			}
		}
		$page_title = 'Elements: Delete “' . $page_request->response['payload']['name'] . '”';
	} else {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
}
?>