<?
$logout_request = new CASHRequest();
$logout_request->sessionClearAllPersistent();

header('Location: ' . ADMIN_WWW_BASE_PATH . '/')
?>