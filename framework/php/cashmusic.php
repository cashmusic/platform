<?php
/**
 *
 * This is the framework bootstrap script. It preps the environment (strips out 
 * stupid shit like magic quotes), includes required classes, and instantiates
 * a CASH request ready to use — pre-populated with any REQUEST data that may
 * have been passed to the page. 
 *
 * (Usage: included at the top of all pages.)
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/

// remove magic quotes, never call them "magic" in front of your friends
if (get_magic_quotes_gpc()) {
    function stripslashes_from_gpc(&$value) {$value = stripslashes($value);}
    $gpc = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    array_walk_recursive($gpc, 'stripslashes_from_gpc');
	unset($gpc);
}

// set up autoload for core classes
function cash_autoloadCore($classname) {
	foreach (array('/classes/core/','/classes/seeds/') as $location) {
		$file = CASH_PLATFORM_ROOT.$location.$classname.'.php';
		if (file_exists($file)) {
			require_once($file);
		}
	}
}
spl_autoload_register('cash_autoloadCore');

// define constants (use sparingly!)
$root = dirname(__FILE__);
define('CASH_PLATFORM_ROOT', $root);

// define cash_embedElement function
// to the children of the future: move this into the CASHSystem class for clarity
// slightly tricky because we'll need to rely on the global-scope $cash_primary_request
function cash_embedElement($element_id) {
	global $cash_primary_request;
	$cash_body_request = new CASHRequest(
		array(
			'cash_request_type' => 'element', 
			'cash_action' => 'getmarkup',
			'id' => $element_id, 
			'status_uid' => $cash_primary_request->response['status_uid']
		)
	);
	echo $cash_body_request->response['payload'];
}

// fire up the platform
$cash_primary_request = new CASHRequest();
?>