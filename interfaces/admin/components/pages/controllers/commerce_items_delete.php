<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/commerce/items/');
}

if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$item_delete_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'commerce',
			'cash_action' => 'deleteitem',
			'id' => $request_parameters[0]
		)
	);
	error_log($item_delete_response['status_uid']);
	if ($item_delete_response['status_uid'] == 'commerce_deleteitem_200') {
		AdminHelper::formSuccess('Success. Deleted.','/commerce/items/');
	}
}
$cash_admin->page_data['title'] = 'Commerce: Delete item';

$cash_admin->setPageContentTemplate('delete_confirm');
?>
