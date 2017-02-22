<?php
if (isset($_GET['cash_request_type']) || isset($_POST['cash_request_type'])) {
	require_once(dirname(__FILE__) . '/request/constants.php');
	require_once(CASH_PLATFORM_PATH);
} else {
	// redirect to the admin
	header('Location: ./../');
}
?>
