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
$admin_primary_seed_request = new SeedRequest();

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
		$admin_primary_seed_request->sessionSetPersistent('cash_actual_user',$login_request->response['payload']);
		$admin_primary_seed_request->sessionSetPersistent('cash_effectiveuser',$login_request->response['payload']);
		if ($requested_filename == 'logout.php') {
			header('Location: ' . WWW_BASE_PATH);
			exit;
		}
	} else {
		$admin_primary_seed_request->sessionClearAllPersistent();
	}
}

if ($admin_primary_seed_request->sessionGetPersistent('cash_actual_user')) {
	if (file_exists($pages_path . 'definitions/' . $requested_filename)) {
		include($pages_path . 'definitions/' . $requested_filename);
	} else {
		include($pages_path . 'definitions/error.php');
	}
	include(ADMIN_BASE_PATH . '/includes/ui/default/top.php');
	if (file_exists($pages_path . 'markup/' . $requested_filename)) {
		include($pages_path . 'markup/' . $requested_filename);
	} else {
		include($pages_path . 'markup/error.php');
	}
	include(ADMIN_BASE_PATH . '/includes/ui/default/bottom.php');
} else {
	include(ADMIN_BASE_PATH . '/includes/ui/default/login.php');
}
?>