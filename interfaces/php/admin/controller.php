<?php
/***************************************************************************************************
 *
 * The CASH admin controller — primary routing for the admin webapp
 *
 * @package admin.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 ***************************************************************************************************/

if(strrpos($_SERVER['REQUEST_URI'],'controller.php') !== false) {
	header('Location: ./');
	exit;
}



/***************************************************************************************************
 *
 * INCLUDES AND STARTUP
 *
 ***************************************************************************************************/
require_once(dirname(__FILE__) . '/constants.php');

// instead of the previous require_once(CASH_PLATFORM_PATH) call, we manually
// load CASHSystem and set admin_primary_cash_request to the first CASHRequest set
include_once(dirname(CASH_PLATFORM_PATH) . '/classes/core/CASHSystem.php');
include_once(dirname(CASH_PLATFORM_PATH) . '/lib/mustache/Mustache.php');
$admin_primary_cash_request = CASHSystem::startUp(true);

// admin-specific autoloader
function cash_admin_autoloadCore($classname) {
	$file = ADMIN_BASE_PATH . '/classes/'.$classname.'.php';
	if (file_exists($file)) {
		require_once($file);
	}
}
spl_autoload_register('cash_admin_autoloadCore');

// make an object to use throughout the pages
$cash_admin = new AdminCore($admin_primary_cash_request->sessionGet('cash_effective_user'),$admin_primary_cash_request);
$cash_admin->mustache_groomer = new Mustache;
$cash_admin->page_data['www_path'] = ADMIN_WWW_BASE_PATH;
$cash_admin->page_data['fullredraw'] = false;

// basic script vars
$pages_path = ADMIN_BASE_PATH . '/components/pages/';
$request_parameters = null;
$admin_theme = 'default';

// set AJAX or not:
$cash_admin->page_data['data_only'] = isset($_REQUEST['data_only']);

// basic rendering options based on optional constants from constants.php
$cash_admin->page_data['jquery_url'] = (defined('JQUERY_URL')) ? JQUERY_URL : ADMIN_WWW_BASE_PATH . '/ui/default/assets/scripts/jquery-1.8.2.min.js';
$cash_admin->page_data['img_base_url'] = (defined('JQUERY_URL')) ? IMAGE_CDN : ADMIN_WWW_BASE_PATH;

// check for TOS and privacy stuff
$cash_admin->page_data['showterms'] = file_exists(ADMIN_BASE_PATH . '/terms.md');
$cash_admin->page_data['showprivacy'] = file_exists(ADMIN_BASE_PATH . '/privacy.md');



/***************************************************************************************************
 *
 * USER LOGIN
 * 
 * check for logged-in status and try to handle any login attempt BEFORE we deal with rendering the
 * page so we show the proper status, etc.
 *
 ***************************************************************************************************/
$logged_in = $admin_primary_cash_request->sessionGet('cash_actual_user');
if (!$logged_in) {
	$cash_admin->page_data['login_message'] = 'Hello.';
	if (isset($_POST['login'])) {
		$browseridassertion = false;
		if (isset($_POST['browseridassertion'])) {
			if ($_POST['browseridassertion'] != -1) {
				$browseridassertion = $_POST['browseridassertion'];
			}
		}
		$login_details = AdminHelper::doLogin($_POST['address'],$_POST['password'],true,$browseridassertion);
		if ($login_details !== false) {
			$admin_primary_cash_request->startSession();
			$admin_primary_cash_request->sessionSet('cash_actual_user',$login_details);
			$admin_primary_cash_request->sessionSet('cash_effective_user',$login_details);
			$cash_admin->effective_user_id = $login_details;
			if ($browseridassertion) {
				$address = CASHSystem::getBrowserIdStatus($browseridassertion);
			} else {
				$address = $_POST['address'];
			}
			$admin_primary_cash_request->sessionSet('cash_effective_user_email',$address);
			$cash_admin->page_data['fullredraw'] = true;
			$logged_in = $login_details;

			// handle initial login chores
			$cash_admin->runAtLogin();
		} else {
			$admin_primary_cash_request->sessionClearAll();
			$cash_admin->page_data['login_message'] = 'Try Again.';
			$cash_admin->page_data['login_error'] = true;
		}
	}
}



/***************************************************************************************************
 *
 * ROUTING
 * 
 * grab path from .htaccess redirect, determine the appropriate route, parse out 
 * additional parameters for the page to use
 *
 ***************************************************************************************************/
if ($_REQUEST['p'] && ($_REQUEST['p'] != realpath(ADMIN_BASE_PATH)) && ($_REQUEST['p'] != '_')) {
	$parsed_request = str_replace('/','_',trim($_REQUEST['p'],'/'));
	define('REQUESTED_ROUTE', '/' . trim($_REQUEST['p'],'/') . '/');
	$cash_admin->page_data['requested_route'] = REQUESTED_ROUTE;
	if (file_exists($pages_path . 'controllers/' . $parsed_request . '.php')) {
		define('BASE_PAGENAME', $parsed_request);
		$include_filename = BASE_PAGENAME.'.php';
	} else {
		// cascade through a "failure" — always show the last good true filename and push the
		// remaining request portions into te request_parameters array
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
			}
		} else {
			define('BASE_PAGENAME', '');
			$include_filename = 'error.php';
		}
		// turn the rest of the request into the parameters array 
		// (available to page controllers)
		$request_parameters = array_slice($exploded_request, 0 - (sizeof($exploded_request) - ($fails_at_level)));
	}
} else {
	define('BASE_PAGENAME','mainpage');
	$include_filename = 'mainpage.php';
}



/***************************************************************************************************
 *
 * RENDER PAGE
 * 
 * check for a logged-in user. if found we need to handle page routing and set up the final page
 * four output. if not, we need to handle login page details and set that up for output
 *
 ***************************************************************************************************/
if ($logged_in) {
	// get user-specific settings
	$current_settings = $cash_admin->getUserSettings();

	if (isset($_GET['resetsimplemode'])) {
		if ($_GET['resetsimplemode'] == 'yesplease') {
			$current_settings['use_simple_mode'] = true;
			$cash_admin->setUserSettings($current_settings);
			header('Location: ' . ADMIN_WWW_BASE_PATH);
		}
	}

	// check for simple mode
	$use_simple_mode = false;
	if (isset($current_settings['use_simple_mode']) && $cash_admin->platform_type == 'multi') {
		if ($current_settings['use_simple_mode'] && 
			file_exists(ADMIN_BASE_PATH . '/ui/' . $admin_theme . '/logic/simplemode.php') &&
			file_exists(ADMIN_BASE_PATH . '/ui/' . $admin_theme . '/simple.mustache')
			) {
			$use_simple_mode = true;
		}
	}
	// we need a session
	$admin_primary_cash_request->startSession();
	if ($use_simple_mode) {
		// SIMPLE MODE: specifics contained in the /ui/theme/logic/simplemode.php file
		include(ADMIN_BASE_PATH . '/ui/' . $admin_theme . '/logic/simplemode.php');
		$template_name = 'simple';
	} else {
		// CONTENT / TEMPLATE: use the standard template for logged-in users, start a session, and
		// populate the page_data array for use in the page view and main template
		$template_name = 'template';

		// handle the banner hiding
		if (isset($_GET['hidebanner'])) {
			if (isset($current_settings['banners'][BASE_PAGENAME])) {
				$current_settings['banners'][BASE_PAGENAME] = false;
				$cash_admin->setUserSettings($current_settings);
			}
		}

		// set basic data for the template
		$cash_admin->page_data['user_email'] = $admin_primary_cash_request->sessionGet('cash_effective_user_email');
		$page_menu_details = AdminHelper::getPageMenuDetails();
		$cash_admin->page_data['section_menu'] = $page_menu_details['section_menu'];
		$cash_admin->page_data['ui_title'] = $page_menu_details['page_title'];
		// merge in display links for main template
		$cash_admin->page_data = array_merge($cash_admin->page_data,$page_menu_details['link_text']);
		// global interaction text
		$ui_interaction_text = AdminHelper::getUiText();
		$cash_admin->page_data = array_merge($cash_admin->page_data,$ui_interaction_text);
		// page specifics
		$page_components = AdminHelper::getPageComponents();
		$cash_admin->page_data['ui_page_tip'] = $page_components['pagetip'];
		if (is_array($page_components['labels'])) {
			foreach ($page_components['labels'] as $key => $val) {
				$cash_admin->page_data['label_' . $key] = $val;
			}
		}
		if (is_array($page_components['tooltips'])) {
			foreach ($page_components['tooltips'] as $key => $val) {
				$cash_admin->page_data['tooltip_' . $key] = $val;
			}
		}
		if (is_array($page_components['copy'])) {
			foreach ($page_components['copy'] as $key => $val) {
				$cash_admin->page_data['copy_' . $key] = $val;
			}
		}
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

		// render the content to be passed to final output
		$cash_admin->page_data['content'] = $cash_admin->mustache_groomer->render($cash_admin->page_content_template, $cash_admin->page_data);
	}
} else {
	// SHOW LOGIN PAGE: we're not logged in, so make that happen and handle login page specific logic
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
			$cash_admin->page_data['legal_markup'] .= '<br /><br /><br /><h4>Terms of service</h4>';
			$cash_admin->page_data['legal_markup'] .= Markdown(file_get_contents(ADMIN_BASE_PATH . '/terms.md'));
		}
		if (isset($cash_admin->page_data['showprivacy'])) {
			$cash_admin->page_data['legal_markup'] .= '<br /><br /><br /><h4>Privacy policy</h4>';
			$cash_admin->page_data['legal_markup'] .= Markdown(file_get_contents(ADMIN_BASE_PATH . '/privacy.md'));
		}
	}

	// this for returning password reset people:
	if (isset($_GET['dopasswordreset'])) {
		// minimum password length
		$cash_admin->page_data['minimum_password_length'] = (defined('MINIMUM_PASSWORD_LENGTH')) ? MINIMUM_PASSWORD_LENGTH : 10;

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

	// and this for the actual password reset after return folks submit the reset form:
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
				$change_response = $cash_admin->requestAndStore(
					array(
						'cash_request_type' => 'system', 
						'cash_action' => 'setlogincredentials',
						'user_id' => $id_response['payload'], 
						'address' => $_POST['address'], 
						'password' => $_POST['new_password']
					)
				);
				if ($change_response['payload'] !== false) {
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



/***************************************************************************************************
 *
 * FINAL OUTPUT
 * 
 * put it all together and spit it all out
 *
 ***************************************************************************************************/
if ($cash_admin->page_data['data_only']) {
	// data_only means we're working with AJAX requests, 
	// so dump valid JSON to the browser for the script to parse
	if (!AdminHelper::getPersistentData('cash_effective_user')) {
		// set to a full redraw if we don't have session data (aka: we just logged out)
		$cash_admin->page_data['fullredraw'] = true;
	}
	$cash_admin->page_data['fullcontent'] = $cash_admin->mustache_groomer->render(file_get_contents(ADMIN_BASE_PATH . '/ui/' . $admin_theme . '/' . $template_name . '.mustache'), $cash_admin->page_data);
	if (!headers_sent()) {
		header('Content-Type: application/json');
	}
	echo json_encode($cash_admin->page_data);
} else {
	// magnum p.i. = sweet {{mustache}} > don draper
	echo $cash_admin->mustache_groomer->render(file_get_contents(ADMIN_BASE_PATH . '/ui/' . $admin_theme . '/' . $template_name . '.mustache'), $cash_admin->page_data);
}
?>