<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/people/lists/');
}

if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$delete_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'deletelist',
			'list_id' => $request_parameters[0]
		)
	);
	if ($delete_response['status_uid'] == 'people_deletelist_200') {
		if (isset($_REQUEST['redirectto'])) {
			AdminHelper::formSuccess('Success. Deleted.',$_REQUEST['redirectto']);
		} else {
			AdminHelper::formSuccess('Success. Deleted.','/people/lists/');
		}
	}
}
$cash_admin->page_data['title'] = 'People: Delete list';

$cash_admin->setPageContentTemplate('delete_confirm');
?>