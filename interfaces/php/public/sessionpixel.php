<?php
	if (isset($_GET['session_id'])) {
		CASHSystem::startSession(true,$_GET['session_id']);
	}
	header('Content-Type: image/gif');
	echo base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
?>