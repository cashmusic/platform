<?php
if (@file_exists(CASH_PLATFORM_ROOT.'/elements' . '/' . $page_request->response['payload']['type'] . '/edit.php')) {
	include(CASH_PLATFORM_ROOT.'/elements' . '/' . $page_request->response['payload']['type'] . '/edit.php');
} else {
	$page_error = "Could not find the edit.php file for this .";
}
?>