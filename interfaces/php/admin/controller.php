<?php
if(strrpos($_SERVER['REQUEST_URI'],'controller.php') !== false) {
	header('Location: ./');
	exit;
}
require_once('./constants.php');

require_once(CASH_PLATFORM_PATH);
$pages_path = ADMIN_BASE_PATH . '/components/pages/';
$admin_primary_cash_request = new CASHRequest();
$request_parameters = null;

// admin-specific autoloader
function cash_admin_autoloadCore($classname) {
	$file = ADMIN_BASE_PATH . '/classes/'.$classname.'.php';
	if (file_exists($file)) {
		require_once($file);
	}
}
spl_autoload_register('cash_admin_autoloadCore');

// grab path from .htaccess redirect
if ($_REQUEST['p'] && ($_REQUEST['p'] != realpath(ADMIN_BASE_PATH))) {
	$parsed_request = str_replace('/','_',trim($_REQUEST['p'],'/'));
	if (file_exists($pages_path . 'views/' . $parsed_request . '.php')) {
		define('BASE_PAGENAME', $parsed_request);
		$include_filename = BASE_PAGENAME.'.php';
	} else {
		// cascade through a "failure" to see if it is a true bad request, or a page requested
		// with parameters requested — always show the last good true filename and push the
		// remaining request portions into te request_parameters array
		if (strpos($parsed_request,'_') !== false) {
			$fails_at_level = 0;
			$successful_request = '';
			$exploded_request = explode('_',$parsed_request);
			for($i = 0, $a = sizeof($exploded_request); $i < $a; ++$i) {
				if ($i > 0) {
					$test_request = $successful_request . '_' . $exploded_request[$i];
				} else {
					$test_request = $successful_request . $exploded_request[$i];
				}
				if (file_exists($pages_path . 'views/' . $test_request . '.php')) {
					$successful_request = $test_request;
				} else {
					$fails_at_level = $i;
					break;
				}
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

$run_login_scripts = false;

// make an object to use throughout the pages
$cash_admin = new AdminCore($admin_primary_cash_request->sessionGet('cash_effective_user'));
$cash_admin->page_data['www_path'] = ADMIN_WWW_BASE_PATH;

// if a login needs doing, do it
$cash_admin->page_data['login_message'] = 'Log In';
if (isset($_POST['login'])) {
	if ($_POST['browseridassertion'] == '-1') {
		$browseridassertion = false;
	} else {
		$browseridassertion = $_POST['browseridassertion'];
	}
	$login_details = AdminHelper::doLogin($_POST['address'],$_POST['password'],true,$browseridassertion);
	if ($login_details !== false) {
		$admin_primary_cash_request->sessionSet('cash_actual_user',$login_details);
		$admin_primary_cash_request->sessionSet('cash_effective_user',$login_details);
		if ($browseridassertion) {
			$address = CASHSystem::getBrowserIdStatus($browseridassertion);
		} else {
			$address = $_POST['address'];
		}
		$admin_primary_cash_request->sessionSet('cash_effective_user_email',$address);
		
		$run_login_scripts = true;
		
		if ($include_filename == 'logout.php') {
			header('Location: ' . ADMIN_WWW_BASE_PATH);
			exit;
		}
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

// handle the banner hiding
if (isset($_GET['hidebanner'])) {
	$current_settings = $cash_admin->getUserSettings();
	if (isset($current_settings['banners'][BASE_PAGENAME])) {
		$current_settings['banners'][BASE_PAGENAME] = false;
		$cash_admin->setUserSettings($current_settings);
	}
}

// include Mustache because you know it's time for that
include(ADMIN_BASE_PATH . '/lib/mustache.php/Mustache.php');
$pencil_thin = new Mustache;

// finally, output the template and page-specific markup (checking for current login)
if ($admin_primary_cash_request->sessionGet('cash_actual_user')) {
	// start buffering output
	ob_start();
	// set basic data for the template
	$cash_admin->page_data['user_email'] = $admin_primary_cash_request->sessionGet('cash_effective_user_email');
	$cash_admin->page_data['title'] = AdminHelper::getPageTitle();
	$cash_admin->page_data['page_tip'] = AdminHelper::getPageTipsString();
	$cash_admin->page_data['section_menu'] = AdminHelper::buildSectionNav();
	// set empty uid/code, then set if found
	$cash_admin->page_data['status_code'] = (isset($_SESSION['cash_last_response'])) ? $_SESSION['cash_last_response']['status_code']: '';
	$cash_admin->page_data['status_uid'] = (isset($_SESSION['cash_last_response'])) ? $_SESSION['cash_last_response']['status_uid']: '';
	// figure out the section color and current section name:
	$cash_admin->page_data['specialcolor'] = '';
	$exploded_base = explode('_',BASE_PAGENAME);
	$cash_admin->page_data['section_name'] = $exploded_base[0];
	if ($exploded_base[0] == 'elements') {
		$cash_admin->page_data['specialcolor'] = ' usecolor1';
	} elseif ($exploded_base[0] == 'assets') {
		$cash_admin->page_data['specialcolor'] = ' usecolor2';
	} elseif ($exploded_base[0] == 'people') {
		$cash_admin->page_data['specialcolor'] = ' usecolor3';
	} elseif ($exploded_base[0] == 'commerce') {
		$cash_admin->page_data['specialcolor'] = ' usecolor4';
	} elseif ($exploded_base[0] == 'calendar') {
		$cash_admin->page_data['specialcolor'] = ' usecolor5';
	}
	// set true/false for each section being current
	$cash_admin->page_data['current_elements'] = ($exploded_base[0] == 'elements') ? true: false;
	$cash_admin->page_data['current_assets'] = ($exploded_base[0] == 'assets') ? true: false;
	$cash_admin->page_data['current_people'] = ($exploded_base[0] == 'people') ? true: false;
	$cash_admin->page_data['current_commerce'] = ($exploded_base[0] == 'commerce') ? true: false;
	$cash_admin->page_data['current_calendar'] = ($exploded_base[0] == 'calendar') ? true: false;
	// include controller/view for current page (if they each exist — technically the controller is optional)
	if (file_exists($pages_path . 'controllers/' . $include_filename)) include($pages_path . 'controllers/' . $include_filename);
	if (file_exists($pages_path . 'views/' . $include_filename)) include($pages_path . 'views/' . $include_filename);
	// push buffer contents to "content" and stop buffering
	$cash_admin->page_data['content'] = ob_get_contents();
	ob_end_clean();
	
	// now let's get our {{mustache}} on
	echo $pencil_thin->render(file_get_contents(ADMIN_BASE_PATH . '/ui/default/template.mustache'), $cash_admin->page_data);
} else {
	$cash_admin->page_data['browser_id_js'] = CASHSystem::getBrowserIdJS();
	// magnum p.i. = sweet {{mustache}} > don draper
	echo $pencil_thin->render(file_get_contents(ADMIN_BASE_PATH . '/ui/default/login.mustache'), $cash_admin->page_data);
}
?>