<?php
/**
 * Takes a snapshot of all currently allowed plant requests and their parameters
 *
 * Run from the command line: php profile_api.php
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */
if ($handle = opendir(dirname(__FILE__) . '/../../../framework/php/classes/plants')) {
	// found the plants, initiate CASH bootstrap
	include(dirname(__FILE__) . '/../../../framework/php/cashmusic.php');
	// go through plant directory, excluding any files with an '__' filename
	$api_profile = array(
		'version' => CASHRequest::$version,
		'timestamp' => time(),
		'request_types' => array()
	);
	while (false !== ($entry = readdir($handle))) {
		if ($entry != "." && $entry != "..") {
			if (substr($entry,0,2) !== '__') {
				// include the plant class and fire it up
				include(dirname(__FILE__) . '/../../../framework/php/classes/plants/' . $entry);
				$classname = str_replace('.php','',$entry);
				$obj = new $classname('direct',false);
				// run the profiler
				$profile = $obj->profileRequests();
				if ($profile !== false) {
					$request_type = strtolower(str_replace('Plant','',$classname));
					$api_profile['request_types'][$request_type] = $profile;
				}
			}
		}
	}
	closedir($handle);
	
	file_put_contents(dirname(__FILE__) . '/releaseprofiles/release_' . CASHRequest::$version . '_requests.json',json_encode($api_profile));
}
?>
