<?php
$all_order_details = false;
$orders_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getordersforuser',
		'user_id' => $cash_admin->effective_user_id
	)
);

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
					'id' => '#' . str_pad($order_details['id'],6,0,STR_PAD_LEFT),
					'date' => CASHSystem::formatTimeAgo((int)$order_date) . '<br /><a href="' . ADMIN_WWW_BASE_PATH . '/commerce/orders/view/' . $order_details['id'] . '">details</a>',
					'customer' => $order_details['customer_details']['display_name'] . '<br /><a href="mailto:' . $order_details['customer_details']['email_address'] . '">' . $order_details['customer_details']['email_address'] . '</a>',
					'items' => str_replace('\n','<br />',$order_details['order_totals']['description']),
					'gross' => '$' . sprintf("%01.2f",$order_details['gross_price']),
					'net' => '$' . sprintf("%01.2f",$order_details['gross_price'] - $order_details['service_fee'])
				);
			}
		}
	}
	if (count($all_order_details) > 0) {
		$cash_admin->page_data['all_order_details'] = new ArrayIterator($all_order_details);
	}
}

$cash_admin->setPageContentTemplate('commerce_orders');
?>