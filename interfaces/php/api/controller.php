<?php
/**
 * The main API controller
 *
 * @package api.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */
$cashmusic_root = realpath(dirname(__FILE__).'/../../../framework/php');

$cash_settings = json_decode(getenv('cashmusic_platform_settings'),true);
// env settings allow use on multi-server, multi-user instances
if ($cash_settings) {
	// thanks to json_decode this will be null if the 
	if (isset($cash_settings['platforminitlocation'])) {
		$cashmusic_root = str_replace('/cashmusic.php', '', $cash_settings['platforminitlocation']);
	}	
}
define('CASH_PLATFORM_ROOT', $cashmusic_root);

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