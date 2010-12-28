<?php
// set initial variabes
$root_path = realpath('.');
$interface_path = $root_path . '/interface/';
define('WWW_BASE_PATH', '/admin');

// grab path from .htaccess redirect
if (isset($_REQUEST['p'])) {
	$requested_content = str_replace('/','_',trim($_REQUEST['p'],'/')).'.php';
} else {
	$requested_content = 'default.php';
}

session_start();

if (file_exists($interface_path . $requested_content)) {
	include($interface_path . $requested_content);
} else {
	include($interface_path . 'error.php');
}
include($root_path . '/includes/top.php');
if (file_exists($interface_path . 'content/' . $requested_content)) {
	include($interface_path . 'content/' . $requested_content);
} else {
	include($interface_path . 'content/' . 'error.php');
}
include($root_path . '/includes/bottom.php');
?>
