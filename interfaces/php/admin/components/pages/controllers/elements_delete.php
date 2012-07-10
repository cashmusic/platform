<?php
if (!$request_parameters) {
	AdminHelper::controllerRedirect('/elements/view/');
}

$page_request = new CASHRequest(
	array(
		'cash_request_type' => 'element', 
		'cash_action' => 'getelement',
		'id' => $request_parameters[0]
	)
);

if ($page_request->response['status_uid'] == 'element_getelement_200') {
	
	$elements_data = AdminHelper::getElementsData();
	$effective_user = AdminHelper::getPersistentData('cash_effective_user');
	
	if ($page_request->response['payload']['user_id'] == $effective_user) {
		if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
			$element_delete_request = new CASHRequest(
				array(
					'cash_request_type' => 'element', 
					'cash_action' => 'deleteelement',
					'id' => $request_parameters[0]
				)
			);
			if ($element_delete_request->response['status_uid'] == 'element_deleteelement_200') {
				if (isset($_REQUEST['redirectto'])) {
					AdminHelper::formSuccess('Success. Deleted.',$_REQUEST['redirectto']);
				} else {
					AdminHelper::formSuccess('Success. Deleted.','/elements/view/');
				}
			}
		}
		$cash_admin->page_data['title'] = 'Elements: Delete “' . $page_request->response['payload']['name'] . '”';
	} else {
		AdminHelper::controllerRedirect('/elements/view/');
	}
} else {
	AdminHelper::controllerRedirect('/elements/view/');
}

$cash_admin->setPageContentTemplate('delete_confirm');
?>