<?php
/**
 * Upgrade script: v4 to v5
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */

// $current_version = 2;
$upgrade_failure = false;

if ($current_version == 4) {
	$api_dir = rtrim(dirname($_SERVER['REQUEST_URI']),'/') . '/api';
	$public_dir = rtrim(dirname($_SERVER['REQUEST_URI']),'/') . '/public';

	if (
		!findReplaceInFile('./update/interfaces/php/api/.htaccess','RewriteBase /interfaces/php/api','RewriteBase ' . $api_dir) || 
		!findReplaceInFile('./update/interfaces/php/public/request/.htaccess','RewriteBase /interfaces/php/public/request','RewriteBase ' . $public_dir . '/request') || 
		
		!findReplaceInFile('./update/interfaces/php/public/request/constants.php','$cashmusic_root = dirname(__FILE__) . "/../../../../framework/php/cashmusic.php','$cashmusic_root = "' . $cash_root_location . '/cashmusic.php') || 
		!findReplaceInFile('./update/interfaces/php/api/controller.php',"define('CASH_PLATFORM_ROOT', dirname(__FILE__).'/../../../framework/php","define('CASH_PLATFORM_ROOT', '" . $cash_root_location)
	) {
		$upgrade_failure = true;
	}
}
?>