<?php
if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$delete_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'deletelogin',
			'address' => $admin_primary_cash_request->sessionGet('cash_effective_user_email')
		)
	);
	if ($delete_response['status_uid'] == 'system_deletelogin_200') {

		AdminHelper::formSuccess('Success. Deleted.','/logout/');
	}
}
$cash_admin->page_data['title'] = 'Account: Delete account';

$cash_admin->setPageContentTemplate('delete_confirm');
?>