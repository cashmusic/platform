<?php
$items_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getitemsforuser',
		'user_id' => $cash_admin->effective_user_id,
	)
);

$orders_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getordersforuser',
		'user_id' => $cash_admin->effective_user_id,
		'max_returned' => 6
	)
);

// banner stuff
$settings = $cash_admin->getUserSettings();
if ($settings['banners'][BASE_PAGENAME]) {
	$cash_admin->page_data['banner_title_content'] = '<b>sell</b> your music<br />review and <b>fulfill</b> orders';
	$cash_admin->page_data['banner_main_content'] = 'Here’s where you’ll define products and special offers, check on orders, manage fulfillment, and tracks overall sales. Connect to your Paypal account and off you go.';
}

if (is_array($items_response['payload'])) {
	$cash_admin->page_data['items_all'] = new ArrayIterator($items_response['payload']);
}

if (is_array($orders_response['payload'])) {
	$all_order_details = array();
	foreach ($orders_response['payload'] as $order) {
		if ($order['canceled'] == 0) {
			
			$order_details_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'commerce', 
					'cash_action' => 'getorder',
					'id' => $order['id'],
					'deep' => true
				)
			);
			
			$order_details = $order_details_response['payload'];
			if ($order_details['successful']) {
				$order_date = $order_details['creation_date'];
				if ($order_details['creation_date']) {
					$order_date = $order_details['modification_date'];
				}
				
				$all_order_details[] = array(
					'id' => $order_details['id'],
					'number' => '#' . str_pad($order_details['id'],6,0,STR_PAD_LEFT),
					'date' => CASHSystem::formatTimeAgo((int)$order_date),
					'items' => str_replace('\n','<br />',$order_details['order_totals']['description']),
					'gross' => '$' . sprintf("%01.2f",$order_details['gross_price']),
				);
			}
		}
	}
	if (count($all_order_details) > 0) {
		$cash_admin->page_data['orders_recent'] = new ArrayIterator($all_order_details);
	}
}

$cash_admin->setPageContentTemplate('commerce');
?>