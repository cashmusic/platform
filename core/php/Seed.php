<?php
/**
 *
 * Prep the environment (strip stupid shit like magic quotes...ooh...magic!)
 * Include required classes, execute request/response
 *
 * @package seed.org.cashmusic
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

// begin session
session_cache_limiter('nocache');
$session_length = 1800;
ini_set("session.gc_maxlifetime", $session_length); 
session_start();

// define constants (use sparingly!)
$root = dirname(__FILE__);
define('CASH_PLATFORM_ROOT', $root);
define('CASH_PLATFORM_CURRENT_URL', 'http'.((empty($_SERVER['HTTPS'])&&$_SERVER['SERVER_PORT']!=443)?'':'s').'://'.$_SERVER['HTTP_HOST'].strtok($_SERVER['REQUEST_URI'],'?'));

// required includes
require_once(CASH_PLATFORM_ROOT.'/classes/core/SeedData.php');
require_once(CASH_PLATFORM_ROOT.'/classes/core/PlantBase.php');
require_once(CASH_PLATFORM_ROOT.'/classes/core/SeedBase.php');
require_once(CASH_PLATFORM_ROOT.'/classes/core/SeedRequest.php');
require_once(CASH_PLATFORM_ROOT.'/classes/core/SeedResponse.php');

// define seed_embedElement in global scope
function seed_embedElement($element_id) {
	global $seed_request;
	$seed_body_request = new SeedRequest(
		array(
			'seed_request_type' => 'element', 
			'seed_action' => 'getmarkup',
			'element_id' => $element_id, 
			'status_uid' => $seed_request->response['status_uid']
		)
	);
	echo $seed_body_request->response['payload'];
}

// fire up seed
$seed_request = new SeedRequest();

// check on each load to see if we need to regenerate the session id
if ($seed_request->sessionGetPersistent('session_regenerate_id')) {
	session_regenerate_id(true);
	$seed_request->sessionClearPersistent('session_regenerate_id');
}
?>