<?php
// add unique page settings:
$page_title = 'Platform Settings';
$page_tips = 'This page manages settings for all external services and APIs. Connect to third-party accounts like Twitter, S3, MailChimp, and more.';

$page_memu = array(
	'System Settings' => array(
		'settings/connections/' => 'Connections'
	)
);

$misc_message = false;
if (isset($_POST['domisc'])) {
	CASHSystem::setSystemSetting('timezone',$_POST['timezone']);
	CASHSystem::setSystemSetting('systememail',$_POST['systememail']);
	$misc_message = 'All changed.';
}
$platform_settings = $return = CASHSystem::getSystemSettings();
?>