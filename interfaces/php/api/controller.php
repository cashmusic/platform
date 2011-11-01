<?php
$api_root = dirname(__FILE__);
define('CASH_PLATFORM_ROOT', $api_root.'/../../../framework/php');

// set up autoload for core classes
function cash_autoloadCore($classname) {
	$file = CASH_PLATFORM_ROOT.'/classes/core/'.$classname.'.php';
	if (file_exists($file)) {
		require_once($file);
	}
}
spl_autoload_register('cash_autoloadCore');

// push away anyone who's trying to access the controller directly
if (strrpos($_SERVER['REQUEST_URI'],'controller.php') !== false) {
	header($http_codes[403], true, 403);
	exit;
} else {
	// instantiate the API, pass the request from .htaccess to it
	require_once('./classes/APICore.php');
	new APICore($_REQUEST['p']);
	exit;
}
?>