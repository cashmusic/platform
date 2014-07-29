<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/');
}

if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$delete_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'deletecampaign',
			'id' => $request_parameters[0]
		)
	);
	if ($delete_response['status_uid'] == 'element_deletecampaign_200') {
		if (isset($_REQUEST['redirectto'])) {
			AdminHelper::formSuccess('Success. Deleted.',$_REQUEST['redirectto']);
		} else {
			AdminHelper::formSuccess('Success. Deleted.','/elements/view/');
		}
	}
}
$cash_admin->page_data['title'] = 'Campaigns: Delete campaign';

$cash_admin->setPageContentTemplate('delete_confirm');
?>