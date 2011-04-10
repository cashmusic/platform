<?php
if(strrpos($_SERVER['REQUEST_URI'],'controller.php') !== false) {
  header('Location: ./');
  exit;
}

require('./includes/constants.php');
$pages_path = ADMIN_BASE_PATH . '/includes/pages/';

// grab path from .htaccess redirect
if ($_REQUEST['p'] && ($_REQUEST['p'] != realpath(ADMIN_BASE_PATH))) {
	define('REQUEST_STRING', str_replace('/','_',trim($_REQUEST['p'],'/')));
	$requested_filename = REQUEST_STRING.'.php';
} else {
	define('REQUEST_STRING','');
	$requested_filename = 'dashboard.php';
}

include_once(CASH_PLATFORM_PATH);

if (isset($_POST['login'])) {
	$login_request = new SeedRequest(
		array(
			'seed_request_type' => 'user', 
			'seed_action' => 'validatelogin',
			'address' => $_POST['address'], 
			'password' => $_POST['password']
		)
	);
	if ($login_request->response['payload'] !== false) {
		$_SESSION['cash_actual_user'] = $login_request->response['payload'];
		$_SESSION['cash_effectiveuser'] = $login_request->response['payload'];
	}
}

if (isset($_SESSION['cash_actual_user'])) {
	if (file_exists($pages_path . 'base/' . $requested_filename)) {
		include($pages_path . 'base/' . $requested_filename);
	} else {
		include($pages_path . 'base/error.php');
	}
	include(ADMIN_BASE_PATH . '/includes/ui/default/top.php');
	if (file_exists($pages_path . 'base/content/' . $requested_filename)) {
		include($pages_path . 'base/content/' . $requested_filename);
	} else {
		include($pages_path . 'base/content/error.php');
	}
	include(ADMIN_BASE_PATH . '/includes/ui/default/bottom.php');
} else {
	include(ADMIN_BASE_PATH . '/includes/ui/default/login.php');
}
?>