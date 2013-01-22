<?php
/**
 * The CASH admin controller — primary routing for the admin webapp
 *
 * @package admin.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 */

if(strrpos($_SERVER['REQUEST_URI'],'controller.php') !== false) {
	header('Location: ./');
	exit;
}

// includes
require_once('./constants.php');
require_once(CASH_PLATFORM_PATH);
include_once(dirname(CASH_PLATFORM_PATH) . '/lib/mustache/Mustache.php');

// admin-specific autoloader
function cash_admin_autoloadCore($classname) {
	$file = ADMIN_BASE_PATH . '/classes/'.$classname.'.php';
	if (file_exists($file)) {
		require_once($file);
	}
}
spl_autoload_register('cash_admin_autoloadCore');

// basic script vars
$pages_path = ADMIN_BASE_PATH . '/components/pages/';
$admin_primary_cash_request = new CASHRequest();
$request_parameters = null;
$run_login_scripts = false;

// make an object to use throughout the pages
$cash_admin = new AdminCore($admin_primary_cash_request->sessionGet('cash_effective_user'));
$cash_admin->mustache_groomer = new Mustache;
$cash_admin->page_data['www_path'] = ADMIN_WWW_BASE_PATH;
$cash_admin->page_data['fullredraw'] = false;

// set AJAX or not:
$cash_admin->page_data['data_only'] = false;
if (isset($_REQUEST['data_only'])) {
	$cash_admin->page_data['data_only'] = true;
}

/**
 * USER LOGIN
 * look specifically for a 'login' POST parameter and handle the actual login
 */
$cash_admin->page_data['login_message'] = 'Hello. Log In';
if (isset($_POST['login'])) {
	$browseridassertion = false;
	if (isset($_POST['browseridassertion'])) {
		if ($_POST['browseridassertion'] != -1) {
			$browseridassertion = $_POST['browseridassertion'];
		}
	}
	$login_details = AdminHelper::doLogin($_POST['address'],$_POST['password'],true,$browseridassertion);
	if ($login_details !== false) {
		CASHSystem::startSession();
		$admin_primary_cash_request->sessionSet('cash_actual_user',$login_details);
		$admin_primary_cash_request->sessionSet('cash_effective_user',$login_details);
		$cash_admin->effective_user_id = $login_details;
		if ($browseridassertion) {
			$address = CASHSystem::getBrowserIdStatus($browseridassertion);
		} else {
			$address = $_POST['address'];
		}
		$admin_primary_cash_request->sessionSet('cash_effective_user_email',$address);
		
		$run_login_scripts = true;
		
		$cash_admin->page_data['fullredraw'] = true;
	} else {
		$admin_primary_cash_request->sessionClearAll();
		$cash_admin->page_data['login_message'] = 'Try Again';
		$cash_admin->page_data['login_error'] = true;
	}
}

if ($run_login_scripts) {
	// handle initial login chores
	$cash_admin->runAtLogin();
}

/**
 * ROUTING
 * grab path from .htaccess redirect, determine the appropriate route, parse out 
 * additional parameters
 */
if ($_REQUEST['p'] && ($_REQUEST['p'] != realpath(ADMIN_BASE_PATH)) && ($_REQUEST['p'] != '_')) {
	$parsed_request = str_replace('/','_',trim($_REQUEST['p'],'/'));
	define('REQUESTED_ROUTE', '/' . trim($_REQUEST['p'],'/') . '/');
	$cash_admin->page_data['requested_route'] = REQUESTED_ROUTE;
	if (file_exists($pages_path . 'controllers/' . $parsed_request . '.php')) {
		define('BASE_PAGENAME', $parsed_request);
		$include_filename = BASE_PAGENAME.'.php';
	} else {
		/**
		 * cascade through a "failure" to see if it is a true bad request, or a page requested
		 * with parameters requested — always show the last good true filename and push the
		 * remaining request portions into te request_parameters array
		 */
		if (strpos($parsed_request,'_') !== false) {
			$fails_at_level = 0;
			$last_good_level = 0;
			$successful_request = '';
			$last_request = '';
			$exploded_request = explode('_',$parsed_request);
			for($i = 0, $a = sizeof($exploded_request); $i < $a; ++$i) {
				if ($i > 0) {
					$test_request = $last_request . '_' . $exploded_request[$i];
				} else {
					$test_request = $last_request . $exploded_request[$i];
				}
				if (file_exists($pages_path . 'controllers/' . $test_request . '.php')) {
					$successful_request = $test_request;
					$last_good_level = $i;
				} else {
					if (!$fails_at_level || $fails_at_level < $last_good_level) {
						$fails_at_level = $i;
					}
				}
				$last_request = $test_request;
			}
			if ($fails_at_level == 0) {
				define('BASE_PAGENAME', '');
				$include_filename = 'error.php';
			} else {
				// define page as successful request
				define('BASE_PAGENAME', $successful_request);
				$include_filename = BASE_PAGENAME.'.php';
				// turn the rest of the request into the parameters array
				$request_parameters = array_slice($exploded_request, 0 - (sizeof($exploded_request) - ($fails_at_level)));
			}
		} else {
			define('BASE_PAGENAME', '');
			$include_filename = 'error.php';
		}
	}
} else {
	define('BASE_PAGENAME','mainpage');
	$include_filename = 'mainpage.php';
}

// be sure that $cash_admin->page_data['requested_route'] has been set
if (!isset($cash_admin->page_data['requested_route'])) {
	$cash_admin->page_data['requested_route'] = '/';
}

// check for TOS and privacy stuff
$cash_admin->page_data['showterms'] = false;
$cash_admin->page_data['showprivacy'] = false;
if (file_exists(ADMIN_BASE_PATH . '/terms.md')) {$cash_admin->page_data['showterms'] = true;}
if (file_exists(ADMIN_BASE_PATH . '/privacy.md')) {$cash_admin->page_data['showprivacy'] = true;}

/**
 * RENDER PAGE
 * check for a logged-in user. if found render the final page as routed. if not,
 * render the login page instead.
 */
if ($admin_primary_cash_request->sessionGet('cash_actual_user')) {
	/*********************************
	 *
	 * LOGGED-IN SHOW THE PAGE
	 *
	 *********************************/
	$template_name = 'template';
	CASHSystem::startSession();

	// handle the banner hiding
	if (isset($_GET['hidebanner'])) {
		$current_settings = $cash_admin->getUserSettings();
		if (isset($current_settings['banners'][BASE_PAGENAME])) {
			$current_settings['banners'][BASE_PAGENAME] = false;
			$cash_admin->setUserSettings($current_settings);
		}
	}

	// set basic data for the template
	$cash_admin->page_data['user_email'] = $admin_primary_cash_request->sessionGet('cash_effective_user_email');
	$cash_admin->page_data['ui_title'] = AdminHelper::getPageTitle();
	$cash_admin->page_data['ui_page_tip'] = AdminHelper::getPageTipsString();
	$cash_admin->page_data['section_menu'] = AdminHelper::buildSectionNav();
	// set empty uid/code, then set if found
	$last_reponse = $admin_primary_cash_request->sessionGetLastResponse();
	$cash_admin->page_data['status_code'] = (is_array($last_reponse)) ? $last_reponse['status_code']: '';
	$cash_admin->page_data['status_uid'] = (is_array($last_reponse)) ? $last_reponse['status_uid']: '';
	// figure out the section color and current section name:
	$cash_admin->page_data['specialcolor'] = '';
	$exploded_base = explode('_',BASE_PAGENAME);
	$cash_admin->page_data['section_name'] = $exploded_base[0];
	if ($exploded_base[0] == 'assets') {
		$cash_admin->page_data['specialcolor'] = ' usecolor1';
	} elseif ($exploded_base[0] == 'people') {
		$cash_admin->page_data['specialcolor'] = ' usecolor2';
	} elseif ($exploded_base[0] == 'commerce') {
		$cash_admin->page_data['specialcolor'] = ' usecolor3';
	} elseif ($exploded_base[0] == 'calendar') {
		$cash_admin->page_data['specialcolor'] = ' usecolor4';
	} elseif ($exploded_base[0] == 'elements') {
		$cash_admin->page_data['specialcolor'] = ' usecolor5';
	}
	// set true/false for each section being current
	$cash_admin->page_data['ui_current_elements'] = ($exploded_base[0] == 'elements') ? true: false;
	$cash_admin->page_data['ui_current_assets'] = ($exploded_base[0] == 'assets') ? true: false;
	$cash_admin->page_data['ui_current_people'] = ($exploded_base[0] == 'people') ? true: false;
	$cash_admin->page_data['ui_current_commerce'] = ($exploded_base[0] == 'commerce') ? true: false;
	$cash_admin->page_data['ui_current_calendar'] = ($exploded_base[0] == 'calendar') ? true: false;
	// include controller for current page
	include($pages_path . 'controllers/' . $include_filename);

	// render the content
	$cash_admin->page_data['content'] = $cash_admin->mustache_groomer->render($cash_admin->page_content_template, $cash_admin->page_data);
} else {
	/*********************************
	 *
	 * SHOW LOGIN PAGE
	 *
	 *********************************/
	$cash_admin->page_data['browser_id_js'] = CASHSystem::getBrowserIdJS();
	$template_name = 'login';
	// before we get all awesome and whatnot, detect for password reset stuff. should only happen 
	// with a full page reload, not a data-only one as above
	if (isset($_POST['dopasswordresetlink'])) {
		if (filter_var($_POST['address'], FILTER_VALIDATE_EMAIL)) {
			$reset_key = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'system', 
					'cash_action' => 'setresetflag',
					'address' => $_POST['address']
				)
			);
			$reset_key = $reset_key['payload'];
			if ($reset_key) {
				$reset_message = 'A password reset was requested for this email address. If you didn\'t request the '
							   . 'reset simply ignore this message and no change will be made. To reset your password '
							   . 'follow this link: '
							   . "\n\n"
							   . CASHSystem::getCurrentURL()
							   . '_?dopasswordreset=' . $reset_key . '&address=' . urlencode($_POST['address']) // <-- the underscore for urls ending with a / ...i dunno. probably fixable via htaccess
							   . "\n\n"
							   . 'Thank you.';
				CASHSystem::sendEmail(
					'A password reset has been requested',
					false,
					$_POST['address'],
					$reset_message,
					'Reset your password?'
				);
				$cash_admin->page_data['reset_message'] = 'Thanks. Just sent an email with instructions. Check your SPAM filters if you do not see it soon.';
			} else {
				$cash_admin->page_data['reset_message'] = 'There was an error. Please check the address and try again.';
			}
		}
	}

	if (isset($_GET['showlegal'])) {
		$cash_admin->page_data['legal_markup'] = '';
		if (file_exists(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php')) {
			include_once(CASH_PLATFORM_ROOT . '/lib/markdown/markdown.php');
		}
		if (isset($cash_admin->page_data['showterms'])) {
			$cash_admin->page_data['legal_markup'] .= '<br /><br /><br /><h3>Terms of service</h3>';
			$cash_admin->page_data['legal_markup'] .= Markdown(file_get_contents(ADMIN_BASE_PATH . '/terms.md'));
		}
		if (isset($cash_admin->page_data['showprivacy'])) {
			$cash_admin->page_data['legal_markup'] .= '<br /><br /><br /><h3>Privacy policy</h3>';
			$cash_admin->page_data['legal_markup'] .= Markdown(file_get_contents(ADMIN_BASE_PATH . '/privacy.md'));
		}
	}

	// this for returning password reset people:
	if (isset($_GET['dopasswordreset'])) {
		if (!defined('MINIMUM_PASSWORD_LENGTH')) {
			$cash_admin->page_data['minimum_password_length'] = 10;
		} else {
			$cash_admin->page_data['minimum_password_length'] = MINIMUM_PASSWORD_LENGTH;
		}

		$valid_key = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validateresetflag',
				'address' => $_GET['address'],
				'key' => $_GET['dopasswordreset']
			)
		);
		if ($valid_key) {
			$cash_admin->page_data['reset_key'] = $_GET['dopasswordreset'];
			$cash_admin->page_data['reset_email'] = $_GET['address'];
			$cash_admin->page_data['reset_action'] = CASHSystem::getCurrentURL();
		}
	}

	// and this for the actual password reset after return folks submit:
	if (isset($_POST['finalizepasswordreset'])) {
		$valid_key = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validateresetflag',
				'address' => $_POST['address'],
				'key' => $_POST['key']
			)
		);
		if ($valid_key) {
			$id_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'people', 
					'cash_action' => 'getuseridforaddress',
					'address' => $_POST['address']
				)
			);
			if ($id_response['payload']) {
				$change_request = new CASHRequest(
					array(
						'cash_request_type' => 'system', 
						'cash_action' => 'setlogincredentials',
						'user_id' => $id_response['payload'], 
						'address' => $_POST['address'], 
						'password' => $_POST['new_password']
					)
				);
				if ($change_request->response['payload'] !== false) {
					$cash_admin->page_data['reset_message'] = 'Successfully changed the password. Go ahead and log in.';
				} else {
					$cash_admin->page_data['reset_message'] = 'There was an error setting your password. Please try again.';
				}
			} else {
				$cash_admin->page_data['reset_message'] = 'There was an error setting the password. Please try again.';
			}
		}
	}	
}

// final output
if ($cash_admin->page_data['data_only']) {
	// data_only means we're working with AJAX requests, so dump valid JSON to the browser for the script to parse
	if (!AdminHelper::getPersistentData('cash_effective_user')) {
		// set to a full redraw if we don't have session data (aka: we just logged out)
		$cash_admin->page_data['fullredraw'] = true;
	}
	$cash_admin->page_data['fullcontent'] = $cash_admin->mustache_groomer->render(file_get_contents(ADMIN_BASE_PATH . '/ui/default/' . $template_name . '.mustache'), $cash_admin->page_data);
	if (!headers_sent()) {
		header('Content-Type: application/json');
	}
	echo json_encode($cash_admin->page_data);
} else {
	// magnum p.i. = sweet {{mustache}} > don draper
	echo $cash_admin->mustache_groomer->render(file_get_contents(ADMIN_BASE_PATH . '/ui/default/' . $template_name . '.mustache'), $cash_admin->page_data);
}
?>