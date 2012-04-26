<?php
$all_order_details = false;
$raw_orders = new CASHRequest(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getordersforuser',
		'user_id' => AdminHelper::getPersistentData('cash_effective_user')
	)
);

if (is_array($raw_orders->response['payload'])) {
	$all_order_details = array();
	foreach ($raw_orders->response['payload'] as $order) {
		if ($order['canceled'] == 0) {
			
			$order_details_request = new CASHRequest(
				array(
					'cash_request_type' => 'commerce', 
					'cash_action' => 'getorder',
					'id' => $order['id'],
					'deep' => true
				)
			);
			
			$order_details = $order_details_request->response['payload'];
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
	if (count($all_order_details) == 0) {
		$all_order_details = false;
	}
}

?>