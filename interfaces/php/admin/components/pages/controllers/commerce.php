<?php
$items_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getitemsforuser',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	),
	'all_items'
);

// banner stuff
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	$cash_admin->page_data['banner_title_content'] = '<h2>Commerce</h2><b>sell</b> your music<br />review and <b>fulfill</b> orders';
	$cash_admin->page_data['banner_main_content'] = 'Here’s where you’ll define products and special offers, check on orders, manage fulfillment, and tracks overall sales. Connect to your Paypal account and off you go.';
}

if (is_array($items_response['payload'])) {
	$cash_admin->page_data['items_all'] = new ArrayIterator($items_response['payload']);
}

$cash_admin->setPageContentTemplate('commerce');
?>