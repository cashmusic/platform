<?php

$orders_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce', 
		'cash_action' => 'getordersforuser',
		'user_id' => $cash_admin->effective_user_id,
		'max_returned' => 6
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

$user_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'people', 
		'cash_action' => 'getuser',
		'user_id' => $cash_admin->effective_user_id
	)
);
if (is_array($user_response['payload'])) {
	$current_userdata = $user_response['payload']['data'];
}

$session_news = AdminHelper::getActivity($current_userdata);
if ($session_news) {
	// now set up page variables
	$total_spend = 0;
	$orders_currency = 'USD';
	if (is_array($session_news['activity']['orders'])) {
		foreach ($session_news['activity']['orders'] as $order) {
			$order_contents = json_decode($order['order_contents']);
			if (is_array($order_contents )) {
				foreach ($order_contents as $item) {
					$total_spend = $total_spend + $item->price;
				}
			}
			$orders_currency = $order['currency'];
		}
		$total_spend = round($total_spend); 
	}
	$cash_admin->page_data['dashboard_lists'] = $session_news['activity']['lists'];	

	if ($session_news['activity']['orders']) {
		$cash_admin->page_data['total_orders'] = count($session_news['activity']['orders']);
		if ($cash_admin->page_data['total_orders'] == 1) {
			$cash_admin->page_data['orders_singular'] = true;
		}
	} else {
		$cash_admin->page_data['total_orders'] = false;
	}

	$cash_admin->page_data['total_spend'] = CASHSystem::getCurrencySymbol($orders_currency) . $total_spend;
}

$cash_admin->setPageContentTemplate('commerce_export');
?>