<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_request, $cash_admin);

if (!$request_parameters) {
	AdminHelper::controllerRedirect('/elements/templates/');
}

if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$delete_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'deletetemplate',
			'template_id' => $request_parameters[0]
		)
	);
	if ($delete_response['status_uid'] == 'system_deletetemplate_200') {
		$admin_helper->formSuccess('Success. Deleted.','/elements/templates/');
	}
}
$cash_admin->page_data['title'] = 'Elements: Delete template';

$cash_admin->setPageContentTemplate('delete_confirm');
?>