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
require_once(__DIR__ . '/constants.php');

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
$cash_admin->page_data['public_url'] = CASH_PUBLIC_URL;
$cash_admin->page_data['platform_version'] = CASHRequest::$version;

// basic script vars
$pages_path = ADMIN_BASE_PATH . '/components/pages/';
$request_parameters = null;
$admin_theme = 'default';

// set AJAX or not:
$cash_admin->page_data['data_only'] = isset($_REQUEST['data_only']);

// basic rendering options based on optional constants from constants.php
$cash_admin->page_data['jquery_url'] = (defined('JQUERY_URL')) ? JQUERY_URL : ADMIN_WWW_BASE_PATH . '/ui/default/assets/scripts/jquery.min.js';
$cash_admin->page_data['jqueryui_url'] = (defined('JQUERYUI_URL')) ? JQUERYUI_URL : ADMIN_WWW_BASE_PATH . '/ui/default/assets/scripts/jquery-ui.min.js';
$cash_admin->page_data['cdn_url'] = (defined('CDN_URL')) ? CDN_URL : ADMIN_WWW_BASE_PATH;
$cash_admin->page_data['show_beta'] = (defined('SHOW_BETA')) ? SHOW_BETA : false;

// check for TOS and privacy stuff
$cash_admin->page_data['showterms'] = file_exists(ADMIN_BASE_PATH . '/terms.md');
$cash_admin->page_data['showprivacy'] = file_exists(ADMIN_BASE_PATH . '/privacy.md');

// platform type
if ($cash_admin->platform_type == 'single') {
	$cash_admin->page_data['platform_type_single'] = true;
}

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
	// check for signup
	$cash_admin->page_data['allow_signups'] = (defined('ALLOW_SIGNUPS')) ? ALLOW_SIGNUPS : true;

	// delete/clear sessions
	$admin_primary_cash_request->sessionClearAll();

	$cash_admin->page_data['loginstatus'] = ' login';
	$cash_admin->page_data['login_message'] = 'OK';
	if (isset($_POST['login'])) {
		$login_details = AdminHelper::doLogin($_POST['address'],$_POST['password'],true,false);
		if ($login_details !== false) {
			$admin_primary_cash_request->startSession();
			$admin_primary_cash_request->sessionSet('cash_actual_user',$login_details);
			$admin_primary_cash_request->sessionSet('cash_effective_user',$login_details);
			$cash_admin->effective_user_id = $login_details;
			$address = $_POST['address'];
			$admin_primary_cash_request->sessionSet('cash_effective_user_email',$address);
			$cash_admin->page_data['initiallogin'] = true;
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
define('REQUESTED_ROUTE', '/' . trim($_REQUEST['p'],'/') . '/');
$cash_admin->page_data['requested_route'] = REQUESTED_ROUTE;
if ($_REQUEST['p'] && ($_REQUEST['p'] != realpath(ADMIN_BASE_PATH)) && ($_REQUEST['p'] != '_') && $logged_in) {
	$parsed_request = str_replace('/','_',trim($_REQUEST['p'],'/'));
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
	if ($logged_in) {
		define('BASE_PAGENAME','mainpage');
		$include_filename = 'mainpage.php';
	} else {
		if (REQUESTED_ROUTE == '/terms/') {
			define('BASE_PAGENAME','terms');
			$include_filename = 'terms.php';
		} else if (REQUESTED_ROUTE == '/privacy/') {
			define('BASE_PAGENAME','privacy');
			$include_filename = 'privacy.php';
		} else {
			if (REQUESTED_ROUTE == '/dosignup/') {
				$cash_admin->page_data['open_signup_panel'] = true;
			}
			define('BASE_PAGENAME','login');
			$include_filename = 'login.php';
		}
	}
}
$cash_admin->page_data['template_name'] = BASE_PAGENAME;


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

	// set language session
	AdminHelper::getOrSetLanguage(
		(!empty($_POST['new_language'])) ? $_POST['new_language'] : false
	);

	// we need a session
	$admin_primary_cash_request->startSession();
	$cash_admin->page_data['user_email'] = $admin_primary_cash_request->sessionGet('cash_effective_user_email');

	// store the current route in session
	if (strpos(REQUESTED_ROUTE,'/settings') === false && strpos(REQUESTED_ROUTE,'/account') === false) {
		$cash_admin->page_data['user_email'] = $admin_primary_cash_request->sessionSet('last_route',REQUESTED_ROUTE);
	}
}

// set basic data for the template
$page_menu_details = AdminHelper::getPageMenuDetails();
$cash_admin->page_data['assets_section_menu'] = $page_menu_details['assets_section_menu'];
$cash_admin->page_data['people_section_menu'] = $page_menu_details['people_section_menu'];
$cash_admin->page_data['commerce_section_menu'] = $page_menu_details['commerce_section_menu'];
$cash_admin->page_data['calendar_section_menu'] = $page_menu_details['calendar_section_menu'];
$cash_admin->page_data['ui_title'] = $page_menu_details['page_title'];

// merge in display links for main template
$cash_admin->page_data = array_merge($cash_admin->page_data,$page_menu_details['link_text']);
// global interaction text
$ui_interaction_text = AdminHelper::getUiText();
$cash_admin->page_data = array_merge($cash_admin->page_data,$ui_interaction_text);
// page specifics
$page_components = AdminHelper::getPageComponents();
$cash_admin->page_data['ui_page_tip'] = $page_components['pagetip'];
$cash_admin->page_data['ui_learn_text'] = $page_components['learn'];
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
	$cash_admin->page_data['specialcolor'] = 'usecolor2';
} elseif ($exploded_base[0] == 'people') {
	$cash_admin->page_data['specialcolor'] = 'usecolor3';
} elseif ($exploded_base[0] == 'commerce') {
	$cash_admin->page_data['specialcolor'] = 'usecolor4';
} elseif ($exploded_base[0] == 'calendar') {
	$cash_admin->page_data['specialcolor'] = 'usecolor5';
} elseif ($exploded_base[0] == 'elements') {
	$cash_admin->page_data['specialcolor'] = 'usecolor1';
}
// set true/false for each section being current
//$cash_admin->page_data['ui_current_elements'] = ($exploded_base[0] == 'elements') ? true: false;
$cash_admin->page_data['ui_current_assets'] = ($exploded_base[0] == 'assets') ? true: false;
$cash_admin->page_data['ui_current_people'] = ($exploded_base[0] == 'people') ? true: false;
$cash_admin->page_data['ui_current_commerce'] = ($exploded_base[0] == 'commerce') ? true: false;
$cash_admin->page_data['ui_current_calendar'] = ($exploded_base[0] == 'calendar') ? true: false;
if (
	!$cash_admin->page_data['ui_current_assets'] &&
	!$cash_admin->page_data['ui_current_people'] &&
	!$cash_admin->page_data['ui_current_commerce'] &&
	!$cash_admin->page_data['ui_current_calendar']
) {
	$cash_admin->page_data['ui_current_main'] = true;
	$cash_admin->page_data['section_name'] = 'main';
}
// include controller for current page
if ($include_filename !== '.php') { // TODO: Why is this getting called at odd times with blank include name. Shouldn't happen, no?
	include($pages_path . 'controllers/' . $include_filename);
}

// render the content to be passed to final output
// $cash_admin->page_content_template is set in the included controller
$cash_admin->page_data['content'] = preg_replace('~>\s+<~', '><', $cash_admin->mustache_groomer->render($cash_admin->page_content_template, $cash_admin->page_data));


/***************************************************************************************************
 *
 * FINAL OUTPUT
 *
 * put it all together and spit it all out
 *
 ***************************************************************************************************/
$rendered_content = preg_replace('~>\s+<~', '><', $cash_admin->mustache_groomer->render(file_get_contents(ADMIN_BASE_PATH . '/ui/' . $admin_theme . '/template.mustache'), $cash_admin->page_data));
if ($cash_admin->page_data['data_only']) {
    // data_only means we're working with AJAX requests,
    // so dump valid JSON to the browser for the script to parse
    $cash_admin->page_data['fullcontent'] = $rendered_content;
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode($cash_admin->page_data);
} else {
    // magnum p.i. = sweet {{mustache}} > don draper
    echo $rendered_content;
}
?>
