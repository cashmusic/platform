<?php
// include the necessary bits, define the page directory
// Define constants too
$root = __DIR__;
$cashmusic_root = realpath($root . "/../../framework/cashmusic.php");

if (!file_exists($cashmusic_root)) {
	$cash_settings = json_decode(getenv('cashmusic_platform_settings'),true);
	// env settings allow use on multi-server, multi-user instances
	if ($cash_settings) {
		// thanks to json_decode this will be null if the 
		if (isset($cash_settings['platforminitlocation'])) {
			$cashmusic_root = $_SERVER['DOCUMENT_ROOT'] . $cash_settings['platforminitlocation'];
		}	
	} 
}

define('ADMIN_BASE_PATH', $root);
define('ADMIN_WWW_BASE_PATH', '/admin');
define('CASH_PLATFORM_PATH', $cashmusic_root);
/*********************************************************
 *
 * OPTIONAL SETTINGS
 * (un-comment to set, otherwise defaults will be used.)
 *
 *********************************************************/
define('ALLOW_SIGNUPS',true); // should people be able to sign up from the admin page?
define('CDN_URL','https://cashmusic.org/admin'); // base CDN for some static assets (img tags, mostly)
define('JQUERY_URL','//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'); // CDN for jQuery
define('JQUERYUI_URL','//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js'); // CDN for jQuery UI
define('MINIMUM_PASSWORD_LENGTH',10); // doesn't allow passwords shorter than this value
define('SUBDOMAIN_USERNAMES',true); // subdomain or subdirectory for user URL scheme (x.cashmusic.org v cashmuisc.org/x)
?>