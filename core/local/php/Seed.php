<?php
/**
 *
 * Prep the environment (strip stupid shit like magic quotes...ooh...magic!)
 * Include required classes, execute request/response
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2010, CASH Music
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
session_cache_expire(240);
session_start();

// define constants (use sparingly!)
$root = dirname(__FILE__);
define('SEED_ROOT', $root);
define('SEED_CURRENT_URL', 'http'.((empty($_SERVER['HTTPS'])&&$_SERVER['SERVER_PORT']!=443)?'':'s').'://'.$_SERVER['HTTP_HOST'].strtok($_SERVER['REQUEST_URI'],'?'));

// required includes
require_once(SEED_ROOT.'/classes/core/SeedData.php');
require_once(SEED_ROOT.'/classes/core/PlantBase.php');
require_once(SEED_ROOT.'/classes/core/SeedBase.php');
require_once(SEED_ROOT.'/classes/core/SeedRequest.php');
require_once(SEED_ROOT.'/classes/core/SeedResponse.php');

$seed_request = new SeedRequest();
?>