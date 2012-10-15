<?php
	if (isset($_GET['session_id'])) {
		include(dirname(__FILE__) . '/request/constants.php');
		require_once(CASH_PLATFORM_PATH);
		CASHSystem::startSession(true,$_GET['session_id']);
	}
	header('Content-Type: image/gif');
	echo base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
?>