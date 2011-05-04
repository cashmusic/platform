<?php
if (@file_exists(ADMIN_BASE_PATH.'/components/elements' . '/' . $page_request->response['payload']['type'] . '/edit.php')) {
	include(ADMIN_BASE_PATH.'/components/elements' . '/' . $page_request->response['payload']['type'] . '/edit.php');
} else {
	$page_error = "Could not find the edit.php file for this .";
}
?>