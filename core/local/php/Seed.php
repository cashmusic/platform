<?php
/**
 *
 * Prep the environment (strip stupid shit like magic quotes...ooh...magic!)
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmuisc.org/
 * 
 * scans querystring values to get current page state and inititate PaypalSeed
 * objects where needed and setting a pageState variable to indicate progress
 *
 * Copyright (c) 2010, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/

// if < PHP 5.3, define __DIR__
if (!defined('__DIR__')) { 
	$FILE__ = dirname(__FILE__); 
	define('__DIR__', $FILE__); 
}

// remove magic quotes, never call them "magic" in front of your friends
if (get_magic_quotes_gpc()) {
    function stripslashes_from_gpc(&$value) {$value = stripslashes($value);}
    $gpc = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    array_walk_recursive($gpc, 'stripslashes_from_gpc');
	unset($gpc);
}

// begin session
session_cache_limiter('private_no_expire');
session_cache_expire(15);
session_start();
// be sure that abandonned requests don't get hijacked too easily
if (isset($_SESSION['seed_useragent'])) {
	if $_SESSION['seed_useragent'] != md5($_SERVER['HTTP_USER_AGENT'] . 'mercury') {
		// cancel session? with a request/reponse model the user_agent should never
		// change mid-operation. ditto on a return trip from an external source.
		session_regenerate_id();
		$_SESSION = array();
		session_destroy();
	}
} else {
	$_SESSION['seed_useragent'] = md5($_SERVER['HTTP_USER_AGENT'] . 'mercury');
}

// determine correct request source
if (php_sapi_name() == ‘cli’ && empty($_SERVER['REMOTE_ADDR'])) {
	$incoming = $_SERVER['argv'];
} else {
	$incoming = array_merge($_GET, $_POST);
}

$ini = parse_ilni_file(__DIR__.'/../settings/seed.ini.php');
define('PAYPAL_ADDRESS', $ini['paypal_address']);
define('PAYPAL_KEY', $ini['paypal_key']);
define('PAYPAL_SECRET', $ini['paypal_secret']);
define('PAYPAL_MICRO_ADDRESS', $ini['paypal_micro_address']);
define('PAYPAL_MICRO_KEY', $ini['paypal_micro_key']);
define('PAYPAL_MICRO_SECRET', $ini['paypal_micro_secret']);
define('DB_HOSTNAME', $ini['hostname']);
define('DB_USERNAME', $ini['username']);
define('DB_PASSWORD', $ini['password']);
define('DB_DATABASE', $ini['database']);
define('SMALLEST_ALLOWED_TRANSACTION', $ini['smallest_allowed_transaction']);
?>