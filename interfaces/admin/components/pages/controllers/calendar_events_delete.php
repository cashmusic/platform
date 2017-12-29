<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_request, $cash_admin);

if (!$request_parameters) {
	AdminHelper::controllerRedirect('/calendar/events/');
}


if (isset($_POST['dodelete']) || isset($_REQUEST['modalconfirm'])) {

	$event_delete_request = $admin_request->request('calendar')
	                        ->action('deleteevent')
	                        ->with(['event_id' => $request_parameters[0]])->get();

	if ($event_delete_request->response['status_uid'] == 'calendar_deleteevent_200') {
			$admin_helper->formSuccess('Success. Deleted.','/calendar/');
		} else {
		$admin_helper->formFailure('Error. Something just didn\'t work right.');
	}
}

$cash_admin->setPageContentTemplate('delete_confirm');
?>
