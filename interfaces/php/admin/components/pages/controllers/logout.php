<?php
$logout_request = new CASHRequest(null);
$logout_request->sessionClearAll();

if (!isset($_REQUEST['noredirect'])) {
	AdminHelper::controllerRedirect('/');
}
?>