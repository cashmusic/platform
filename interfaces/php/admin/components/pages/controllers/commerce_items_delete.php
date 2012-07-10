<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/elements/view/');
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
		if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
			$item_delete_request = new CASHRequest(
				array(
					'cash_request_type' => 'commerce', 
					'cash_action' => 'deleteitem',
					'id' => $request_parameters[0]
				)
			);
			if ($item_delete_request->response['status_uid'] == 'commerce_deleteitem_200') {
				if (isset($_REQUEST['redirectto'])) {
					AdminHelper::formSuccess('Success. Deleted.',$_REQUEST['redirectto']);
				} else {
					AdminHelper::formSuccess('Success. Deleted.','/commerce/items/');
				}
			}
		}
		$cash_admin->page_data['title'] = 'Commerce: Delete “' . $page_request->response['payload']['name'] . '”';
	} else {
		AdminHelper::controllerRedirect('/commerce/items/');
	}
} else {
	AdminHelper::controllerRedirect('/commerce/items/');
}

$cash_admin->setPageContentTemplate('delete_confirm');
?>