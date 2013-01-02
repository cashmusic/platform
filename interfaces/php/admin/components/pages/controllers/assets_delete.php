<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/assets/');
}

if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$delete_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'asset', 
			'cash_action' => 'deleteasset',
			'id' => $request_parameters[0]
		)
	);
	if ($delete_response['status_uid'] == 'asset_deleteasset_200') {
		AdminHelper::formSuccess('Success. Deleted.','/assets/');
	}
}
$cash_admin->page_data['title'] = 'Assets: Delete asset';

$cash_admin->setPageContentTemplate('delete_confirm');
?>