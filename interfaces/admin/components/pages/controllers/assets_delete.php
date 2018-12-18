<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_primary_cash_request, $cash_admin);

if (empty($request_parameters)) {
	AdminHelper::controllerRedirect('/assets/');
}

if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$delete_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'deleteasset',
			'id' => $request_parameters[0],
            'connection_id' => isset($request_parameters[1]) ? $request_parameters[1] : 0,
            'user_id' => $admin_helper->getPersistentData('cash_effective_user')
		)
	);
	if ($delete_response['status_uid'] == 'asset_deleteasset_200') {
		$admin_helper->formSuccess('Success. Deleted.','/assets/');
	}
}
$cash_admin->page_data['title'] = 'Assets: Delete asset';

$cash_admin->setPageContentTemplate('delete_confirm');
?>