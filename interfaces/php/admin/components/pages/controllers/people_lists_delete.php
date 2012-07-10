<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/people/lists/');
}

$page_request = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlist',
		'list_id' => $request_parameters[0]
	),
	'getlist'
);

//var_dump($page_request);

if ($page_request['status_uid'] == 'people_getlist_200') {
	
	$elements_data = AdminHelper::getElementsData();
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	
	if ($page_request['payload']['user_id'] == $effective_user) {
		if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
			$list_delete_request = new CASHRequest(
				array(
					'cash_request_type' => 'people', 
					'cash_action' => 'deletelist',
					'list_id' => $request_parameters[0]
				)
			);
			if ($list_delete_request->response['status_uid'] == 'people_deletelist_200') {
				if (isset($_REQUEST['redirectto'])) {
					AdminHelper::formSuccess('Success. Deleted.',$_REQUEST['redirectto']);
				} else {
					AdminHelper::formSuccess('Success. Deleted.','/people/lists/');
				}
			}
		}
		$cash_admin->page_data['title'] = 'People: Delete “' . $page_request->response['payload']['name'] . '”';
	} else {
		AdminHelper::controllerRedirect('/people/lists/');
	}
} else {
	AdminHelper::controllerRedirect('/people/lists/delete/' . $request_parameters[0]);
}

$cash_admin->setPageContentTemplate('delete_confirm');
?>