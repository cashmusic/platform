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

if (get_magic_quotes_gpc()) {
    function stripslashes_from_gpc(&$value) {$value = stripslashes($value);}
    $gpc = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    array_walk_recursive($gpc, 'stripslashes_from_gpc');
	unset($gpc);
}
?>