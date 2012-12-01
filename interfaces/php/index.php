<?php 
/**
 *
 * The main page publishing script for a CASH Music instance. Handles the main 
 * public-facing pages, either the default service page or the user-published 
 * pages (assumes user id = 1 for single-user instances, looks for a 'username')
 * GET parameter for multi-user instances.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/

// include the necessary bits, define the page directory
// Define constants too
$root = dirname(__FILE__);
$cashmusic_root = realpath($root . "/../../framework/php/cashmusic.php");

$cash_settings = json_decode(getenv('cashmusic_platform_settings'),true);
if ($cash_settings) {
	if (isset($cash_settings['platforminitlocation'])) {
		// this one isn't set for single-instance installs, generally. so we use the physical path above
		$cashmusic_root = $_SERVER['DOCUMENT_ROOT'] . $cash_settings['platforminitlocation'];
	}	
}
// launch CASH Music
require_once($cashmusic_root);

// set user_id to false, check for single instance type
$user_id = false;
if (CASHSystem::getSystemSettings('instancetype') == 'single') {
	$user_id = 1; // we can assume 1 for single-user instances
}

// if we've got a username we need to find the id — over-write no matter what. no fallback to user id 1
if (isset($_GET['username'])) {
	$user_request = new CASHRequest(
		array(
			'cash_request_type' => 'people', 
			'cash_action' => 'getuseridforusername',
			'username' => $_GET['username']
		)
	);
	if ($user_request->response['payload']) {
		$user_id = $user_request->response['payload'];
	} else {
		$user_id = false;
	}
}

// if we don't find any user id assume we show a default page
if ($user_id) {
	$template_request = new CASHRequest(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'getnewesttemplate',
			'user_id' => $user_id
		)
	);
	$template = $template_request->response['payload'];

	// with a real user but no template we redirect to the admin
	if ($template) {
		$element_embeds = false; // i know we don't technically need this, but the immaculate variable in preg_match_all freaks me out
		$page_vars = array(); // setting up the array for page variables
		$found_elements = preg_match_all('/{{{element_(.*?)}}}/',$template,$element_embeds, PREG_PATTERN_ORDER);
		if ($found_elements) {

			foreach ($element_embeds[1] as $element_id) {
				ob_start();
				CASHSystem::embedElement($element_id);
				$page_vars['element_' . $element_id] = ob_get_contents();
				ob_end_clean();
			}
			
		}
		// render out the page itself
		echo CASHSystem::renderMustache($template,$page_vars);
	} else {
		// redirect to the admin
		header('Location: ./admin/');
	}
} else {
	/********************************
	 *
	 *  ADD PUBLIC PAGE HERE
	 *
	 ********************************/
}
?>