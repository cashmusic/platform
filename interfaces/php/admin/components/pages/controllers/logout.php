<?
$logout_request = new CASHRequest(null);
$logout_request->sessionClearAll();

AdminHelper::controllerRedirect('/');
?>