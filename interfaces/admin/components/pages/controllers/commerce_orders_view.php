<?php
$effective_user = $cash_admin->effective_user_id;

if ($request_parameters) {

	if (isset($request_parameters[1])) {
		if ($request_parameters[1] == 'fulfilled') {
			$order_details_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'commerce',
					'cash_action' => 'editorder',
					'id' => $request_parameters[0],
					'fulfilled' => 1
				)
			);
			AdminHelper::formSuccess('Changes saved.','/commerce/orders/view/' . $request_parameters[0]);
		}
	}

	if (isset($_POST['ordernotes'])) {
		$order_details_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'editorder',
				'id' => $request_parameters[0],
				'notes' => $_POST['ordernotes']
			)
		);
		AdminHelper::formSuccess('Changes saved.','/commerce/orders/view/' . $request_parameters[0]);
	}

	$order_details_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'commerce',
			'cash_action' => 'getorder',
			'id' => $request_parameters[0],
			'deep' => true
		)
	);
	$order_details = $order_details_response['payload'];
	if ($order_details['user_id'] == $effective_user) {
		$order_details['padded_id'] = str_pad($order_details['id'],6,0,STR_PAD_LEFT);
		$order_details['order_date'] = date("M j, Y, g:i A", $order_details['modification_date']);
		$order_details['formatted_gross_price'] = sprintf("%01.2f",$order_details['gross_price']);
		$order_details['formatted_net_price'] = sprintf("%01.2f",$order_details['gross_price'] - $order_details['service_fee']);
		$order_details['order_connection_details'] = AdminHelper::getConnectionName($order_details['connection_id']) . ' (' . $order_details['connection_type'] . ')';
		//if ($order_details['fulfilled']) { $order_details['order_fulfilled'] = 'yes'; } else { $order_details['order_fulfilled'] = 'no'; }
		$cash_admin->page_data = array_merge($cash_admin->page_data,$order_details);
		$cash_admin->page_data['order_contents'] = new ArrayIterator(json_decode($order_details['order_contents'],true));
		$cash_admin->page_data['customer_display_name'] = $order_details['customer_details']['display_name'];
		$cash_admin->page_data['customer_email_address'] = $order_details['customer_details']['email_address'];
		$cash_admin->page_data['customer_address_country'] = $order_details['customer_details']['address_country'];
		$cash_admin->page_data['notes'] = $order_details['notes'];
		error_log(print_r($order_details,true));
		$cash_admin->page_data['ui_title'] = 'Commerce: Order #' . $order_details['padded_id'];

		$formatted_data_sent = array();
		foreach (json_decode($order_details['data_sent'],true) as $key => $value) {
			$formatted_data_sent[] = array(
				'key' => $key,
				'value' => $value
			);
		}
		$cash_admin->page_data['formatted_data_sent'] = new ArrayIterator($formatted_data_sent);

		$formatted_data_returned = array();
		foreach (json_decode($order_details['data_returned'],true) as $key => $value) {
			$formatted_data_returned[] = array(
				'key' => $key,
				'value' => $value
			);
		}
		$cash_admin->page_data['formatted_data_returned'] = new ArrayIterator($formatted_data_returned);
	} else {
		header('Location: ' . ADMIN_WWW_BASE_PATH . '/commerce/orders/');
	}
} else {
	header('Location: ' . ADMIN_WWW_BASE_PATH . '/commerce/orders/');
}

$cash_admin->setPageContentTemplate('commerce_orders_details');
?>
