<?php
// add unique page settings:
$page_title = 'Platform Settings';
$page_tips = 'This page manages settings for all external services and APIs. Connect to third-party accounts like Twitter, S3, MailChimp, and more.';

$page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
$settings_types_data = $page_data_object->getConnectionTypes();
$settings_for_user = $page_data_object->getAllConnectionsforUser();

if ($request_parameters) {
	$settings_action = $request_parameters[0];
}
?>