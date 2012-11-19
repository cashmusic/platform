<?php
// include the necessary bits, define the page directory
// Define constants too
$root = dirname(__FILE__);
$cashmusic_root = realpath($root . "/../../../framework/php/cashmusic.php");

$cash_settings = json_decode(getenv('cashmusic_platform_settings'),true);
// env settings allow use on multi-server, multi-user instances
if ($cash_settings) {
	// thanks to json_decode this will be null if the 
	if (isset($cash_settings['platforminitlocation'])) {
		$cashmusic_root = $_SERVER['DOCUMENT_ROOT'] . $cash_settings['platforminitlocation'];
	}	
}

define('ADMIN_BASE_PATH', $root);
define('ADMIN_WWW_BASE_PATH', '/interfaces/php/admin');
define('CASH_PLATFORM_PATH', $cashmusic_root);
?>