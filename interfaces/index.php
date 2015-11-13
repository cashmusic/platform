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
 * Copyright (c) 2014, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/

/* SINGLE USER SUPPORT? UNCOMMENT
 * $user_id = 1; // we can assume 1 for single-user instances
 */

$user_id = false;
require_once(__DIR__ . '/admin/constants.php');

// launch CASH Music
require_once($cashmusic_root);

// if we've got a username we need to find the id — over-write no matter what. no fallback to user id 1
if (isset($_GET['subdomain']) || isset($_GET['path'])) {
	//error_log($_GET['subdomain'] . "\n" . $_GET['path'] . "\n" . print_r($_GET,true));

	if ($_GET['subdomain'] !== 'cashmusic.org' &&
		$_GET['subdomain'] !== 'x.cashmusic.org' &&
		$_GET['subdomain'] !== 'localhost.cashmusic.org' &&
		$_GET['subdomain'] !== 'testing.cashmusic.org' &&
		$_GET['subdomain'] !== 'staging.cashmusic.org' &&
		$_GET['subdomain'] !== 'air.cashmusic.org' &&
		SUBDOMAIN_USERNAMES
	) {
		$username = explode('.', $_GET['subdomain']);
		$username = $username[0];
	} else {
		$username = explode('/', trim($_GET['path'],'/'));
		$username = $username[0];
	}

	if ($username) {
		// include the necessary bits, define the page directory
		// Define constants too
		$page_vars = array(); // setting up the array for page variables
		$page_vars['www_path'] = ADMIN_WWW_BASE_PATH;
		$page_vars['jquery_url'] = (defined('JQUERY_URL')) ? JQUERY_URL : ADMIN_WWW_BASE_PATH . '/ui/default/assets/scripts/jquery-1.8.2.min.js';
		$page_vars['cdn_url'] = (defined('CDN_URL')) ? CDN_URL : ADMIN_WWW_BASE_PATH;

		if (stripos($username,'/')) {
			$username = explode('/', $username);
			$username = $username[0];
		}
		$user_request = new CASHRequest(
			array(
				'cash_request_type' => 'people',
				'cash_action' => 'getuseridforusername',
				'username' => $username
			)
		);
		if ($user_request->response['payload']) {
			$user_id = $user_request->response['payload'];
		} else {
			$user_id = false;
		}
	}
}

 // error_log($user_id);

// if we find a user check for a template and render one if found.
if ($user_id) {
	$settings_request = new CASHRequest(
		array(
			'cash_request_type' => 'system',
			'cash_action' => 'getsettings',
			'type' => 'public_profile_template',
			'user_id' => $user_id
		)
	);
	if ($settings_request->response['payload']) {
		$template_id = $settings_request->response['payload'];
	} else {
		$template_id = false;
	}

	$template = false;
	if ($template_id) {
		$template_request = new CASHRequest(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'gettemplate',
				'template_id' => $template_id,
				'user_id' => $user_id
			)
		);
		$template = $template_request->response['payload'];
	}

	// with a real user but no template we redirect to the admin
	if ($template) {
		$element_embeds = false; // i know we don't technically need this, but the immaculate variable in preg_match_all freaks me out
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
		exit();
	} else {
		// redirect to the admin
		header('Location: ./admin/');
	}
}


/***************************************
 *
 *  NOT A USER. DISPLAY MAIN SITE.
 *
 ***************************************/

$cache_request = new CASHRequest();
$cache_request->primeCache();
echo $cache_request->getCachedURL('org.cashmusic.prime', 'pagecache', 'http://prime.cashmusic.org/', 'raw', false);
?>
