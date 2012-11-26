<?php
$list_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getlistsforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

// banner stuff
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	$cash_admin->page_data['banner_title_content'] = 'manage your <b>contacts</b><br />create and maintain <b>lists</b><br />monitor <b>social</b> media';
	$cash_admin->page_data['banner_main_content'] = 'Manage contacts on an individual basis or define lists to use for private login lists, mailing lists, etc.';
}

// lists
if (is_array($list_response['payload'])) {
	$cash_admin->page_data['lists_all'] = new ArrayIterator($list_response['payload']);
}

$cash_admin->page_data['current_date'] = date('m/d/Y');
$cash_admin->setPageContentTemplate('people');
?>