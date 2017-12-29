<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_request, $cash_admin);

if (!$request_parameters) {
	AdminHelper::controllerRedirect('/assets/');
}

if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {

	$delete_response = $admin_request->request('asset')
	                        ->action('deleteasset')
	                        ->with([
                                'id' => $request_parameters[0],
                                'connection_id' => isset($request_parameters[1]) ? $request_parameters[1] : false,
                                'user_id' => $admin_helper->getPersistentData('cash_effective_user')
                            ])->get();

	if ($delete_response['status_uid'] == 'asset_deleteasset_200') {
		$admin_helper->formSuccess('Success. Deleted.','/assets/');
	}
}
$cash_admin->page_data['title'] = 'Assets: Delete asset';

$cash_admin->setPageContentTemplate('delete_confirm');
?>