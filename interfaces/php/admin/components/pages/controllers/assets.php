<?php
// banner stuff
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	$cash_admin->page_data['banner_title_content'] = '<b>upload</b> files<br /><b>organize</b> assets for use<br />add <b>tags</b> and <b>metadata</b>';
	$cash_admin->page_data['banner_main_content'] = 'Enter details about all the files that matter to you, either on a connected S3 account or simple URLs. These assets will be used in the elements you define.';
}

$cash_admin->setPageContentTemplate('assets');
?>