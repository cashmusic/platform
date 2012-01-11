<?
$logout_request = new CASHRequest(null);
$logout_request->sessionClearAll();

header('Location: ' . ADMIN_WWW_BASE_PATH . '/')
?>