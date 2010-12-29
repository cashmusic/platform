<?php
require('./constants.php');
$interface_path = ADMIN_BASE_PATH . '/interface/';

// grab path from .htaccess redirect
if (isset($_REQUEST['p'])) {
	define('REQUEST_STRING', str_replace('/','_',trim($_REQUEST['p'],'/')));
	$requested_filename = REQUEST_STRING.'.php';
} else {
	$requested_filename = 'default.php';
}

session_start();

if (file_exists($interface_path . $requested_filename)) {
	include($interface_path . 'base/' . $requested_filename);
} else {
	include($interface_path . 'base/error.php');
}
include(ADMIN_BASE_PATH . '/includes/top.php');
if (file_exists($interface_path . 'base/content/' . $requested_filename)) {
	include($interface_path . 'base/content/' . $requested_filename);
} else {
	include($interface_path . 'base/content/error.php');
}
include(ADMIN_BASE_PATH . '/includes/bottom.php');
?>