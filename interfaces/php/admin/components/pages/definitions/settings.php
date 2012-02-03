<?php
// add unique page settings:
$page_title = 'Platform Settings';
$page_tips = 'This page manages settings for all external services and APIs. Connect to third-party accounts like Twitter, S3, MailChimp, and more.';

$page_memu = array(
	'System Settings' => array(
		'settings/connections/' => array('Connections','share')
	)
);

$misc_message = false;
if (isset($_POST['domisc'])) {
	CASHSystem::setSystemSetting('timezone',$_POST['timezone']);
	CASHSystem::setSystemSetting('systememail',$_POST['systememail']);
	$misc_message = 'All changed.';
}

$migrate_message = false;
if (isset($_POST['domigrate'])) {
	$new_settings = array (
		'hostname' => $_POST['hostname'],
		'username' => $_POST['adminuser'],
		'password' => $_POST['adminpassword'],
		'database' => $_POST['databasename']
	);
	$migrate_request = new CASHRequest(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'migratedb',
			'todriver' => $_POST['driver'],
			'tosettings' => $new_settings
		)
	);
	if ($migrate_request->response['payload']) {
		$migrate_message = 'Well that happened.';
	} else {
		$migrate_message = 'There was a problem migrating your data.';
	}
}
$platform_settings = $return = CASHSystem::getSystemSettings();
?>