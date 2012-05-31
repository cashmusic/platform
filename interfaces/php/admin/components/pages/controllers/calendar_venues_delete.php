<?php
if (!$request_parameters) {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/calendar/venues/');
}


if (isset($_POST['dodelete']) || isset($_GET['modalconfirm'])) {
	$venue_delete_request = new CASHRequest(
		array(
			'cash_request_type' => 'calendar', 
			'cash_action' => 'deletevenue',
			'venue_id' => $request_parameters[0]
		)
	);
	if ($venue_delete_request->response['status_uid'] == 'calendar_deletevenue_200') {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/calendar/venues/');
	}
}

$cash_admin->setPageContentTemplate('delete_confirm');
?>