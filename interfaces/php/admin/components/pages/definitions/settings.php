<?php
// add unique page settings:
$page_title = 'Platform Settings';
$page_tips = 'This page manages settings for all external services and APIs. Connect to third-party accounts like Twitter, S3, MailChimp, and more.';

include_once(ADMIN_BASE_PATH.'/components/helpers.php');
$settings_types_data = getSettingsTypes();
?>