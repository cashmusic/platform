<?php
if (!$request_parameters) {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/elements/view/');
}

$page_request = new CASHRequest(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getitem',
		'id' => $request_parameters[0]
	)
);

if ($page_request->response['status_uid'] == 'commerce_getitem_200') {
	
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	
	if ($page_request->response['payload']['user_id'] == $effective_user) {
		if (isset($_POST['doitemdelete']) || isset($_GET['modalconfirm'])) {
			$item_delete_request = new CASHRequest(
				array(
					'cash_request_type' => 'commerce', 
					'cash_action' => 'deleteitem',
					'id' => $request_parameters[0]
				)
			);
			if ($item_delete_request->response['status_uid'] == 'commerce_deleteitem_200') {
				header('Location: ' . ADMIN_WWW_BASE_PATH . '/commerce/items/');
			}
		}
		$page_title = 'Commerce: Delete “' . $page_request->response['payload']['name'] . '”';
	} else {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/commerce/items/');
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/commerce/items/');
}
?>