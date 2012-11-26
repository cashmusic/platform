<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/elements/view/');
}

if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$item_delete_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'commerce', 
			'cash_action' => 'deleteitem',
			'id' => $request_parameters[0]
		)
	);
	if ($item_delete_response['status_uid'] == 'commerce_deleteitem_200') {
		if (isset($_REQUEST['redirectto'])) {
			AdminHelper::formSuccess('Success. Deleted.',$_REQUEST['redirectto']);
		} else {
			AdminHelper::formSuccess('Success. Deleted.','/commerce/items/');
		}
	}
}
$cash_admin->page_data['title'] = 'Commerce: Delete “' . $page_request->response['payload']['name'] . '”';

$cash_admin->setPageContentTemplate('delete_confirm');
?>