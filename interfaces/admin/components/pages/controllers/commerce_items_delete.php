<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_primary_cash_request, $cash_admin);

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

	if ($item_delete_response['status_uid'] == 'commerce_deleteitem_200') {
		$admin_helper->formSuccess('Success. Deleted.','/commerce/items/');
	}
}
$cash_admin->page_data['title'] = 'Commerce: Delete item';

$cash_admin->setPageContentTemplate('delete_confirm');
?>
