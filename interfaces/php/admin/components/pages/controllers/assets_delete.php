<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/assets/');
}


if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$asset_delete_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'deleteasset',
			'id' => $request_parameters[0]
		)
	);
	if ($asset_delete_response['status_uid'] == 'asset_deleteasset_200') {
		if (isset($_REQUEST['redirectto'])) {
			AdminHelper::formSuccess('Success. Deleted.',$_REQUEST['redirectto']);
		} else {
			AdminHelper::formSuccess('Success. Deleted.','/assets/');
		}
	}
}

$cash_admin->setPageContentTemplate('delete_confirm');
?>