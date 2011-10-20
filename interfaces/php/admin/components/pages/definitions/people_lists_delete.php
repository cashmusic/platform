<?php
// add unique page settings:
$page_title = 'People: Delete List';
$page_tips = 'Confirm to delete.';

if (!$request_parameters) {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/people/lists/');
}

$page_request = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlistinfo',
		'list_id' => $request_parameters[0]
	),
	'getlistinfo'
);

//var_dump($page_request);

if ($page_request['status_uid'] == 'people_getlistinfo_200') {
	
	$elements_data = AdminHelper::getElementsData();
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	
	if ($page_request['payload']['user_id'] == $effective_user) {
		if (isset($_POST['dolistdelete']) || isset($_GET['modalconfirm'])) {
			$list_delete_request = new CASHRequest(
				array(
					'cash_request_type' => 'people', 
					'cash_action' => 'deletelist',
					'list_id' => $request_parameters[0]
				)
			);
			if ($list_delete_request->response['status_uid'] == 'people_deletelist_200') {
				header('Location: ' . ADMIN_WWW_BASE_PATH . '/people/lists/');
			}
		}
		$page_title = 'People: Delete “' . $page_request->response['payload']['name'] . '”';
	} else {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/people/lists/');
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/people/lists/delete/' . $request_parameters[0]);
}
?>