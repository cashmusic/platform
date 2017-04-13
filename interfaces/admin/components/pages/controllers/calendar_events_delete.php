<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_primary_cash_request, $cash_admin);

if (!$request_parameters) {
	AdminHelper::controllerRedirect('/calendar/events/');
}


if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {
	$event_delete_request = new CASHRequest(
		array(
			'cash_request_type' => 'calendar',
			'cash_action' => 'deleteevent',
			'event_id' => $request_parameters[0]
		)
	);
	if ($event_delete_request->response['status_uid'] == 'calendar_deleteevent_200') {
			$admin_helper->formSuccess('Success. Deleted.','/calendar/');
		} else {
		$admin_helper->formFailure('Error. Something just didn\'t work right.');
	}
}

$cash_admin->setPageContentTemplate('delete_confirm');
?>
