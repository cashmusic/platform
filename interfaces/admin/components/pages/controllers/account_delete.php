<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_request, $cash_admin);

if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {

	$delete_response = $admin_request->request('system')
	                        ->action('deletelogin')
	                        ->with(['address' => $admin_request->sessionGet('cash_effective_user_email')])->get();

	if ($delete_response['status_uid'] == 'system_deletelogin_200') {

		$admin_helper->formSuccess('Success. Deleted.','/logout/');
	}
}
$cash_admin->page_data['title'] = 'Account: Delete account';

$cash_admin->setPageContentTemplate('delete_confirm');
?>