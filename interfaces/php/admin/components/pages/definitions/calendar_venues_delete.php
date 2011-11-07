<?php
// add unique page settings:
$page_title = 'Calendar: Delete Venue';
$page_tips = 'Confirm to delete.';

if (!$request_parameters) {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/calendar/venues/');
}


if (isset($_POST['dovenuedelete']) || isset($_GET['modalconfirm'])) {
	$venue_delete_request = new CASHRequest(
		array(
			'cash_request_type' => 'calendar', 
			'cash_action' => 'deletevenue',
			'id' => $request_parameters[0]
		)
	);
	if ($venue_delete_request->response['status_uid'] == 'calendar_deletevenue_200') {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/calendar/venues/');
	}
}
?>