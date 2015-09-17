<?php
$effective_user = $cash_admin->effective_user_id;

if ($request_parameters) {

	if (isset($_POST['resend_store_url'])) {
		$resend_response = $cash_admin->requestAndStore(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'sendorderreceipt',
				'id' => $request_parameters[0],
				'finalize_url' => $_POST['resend_store_url']
			)
		);
		AdminHelper::formSuccess('Receipt sent!','/commerce/orders/view/' . $request_parameters[0]);
	}

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
			AdminHelper::formSuccess('Order fulfilled.','/commerce/orders/view/' . $request_parameters[0]);
		} /* else if ($request_parameters[1] == 'cancel') {
			$order_cancel_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'commerce',
					'cash_action' => 'cancelorder',
					'id' => $request_parameters[0]
				)
			);
			if ($order_cancel_response['payload']) {
				AdminHelper::formSuccess('Order cancelled.','/commerce/orders/view/' . $request_parameters[0]);
			} else {
				AdminHelper::formFailure('Try again.','/commerce/orders/view/' . $request_parameters[0]);
			}
		} */
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




		$order_contents = json_decode($order_details['order_contents'],true);
		$item_price = 0;
		foreach ($order_contents as $key => &$item) {
			if (!isset($item['qty'])) {
				$item['qty'] = 1;
			}
			$item['price'] = $item['qty'] * $item['price'];
			$item_price += $item['price'];
			$item['price'] = number_format($item['price'],2);

			// TODO: stealing the variant parser from CommercePlant::getOrderTotals
			//       we know this is going to change so no sense streamlining yet
			//       FIX LATER
			if (isset($item['variant'])) {

				preg_match_all("/([a-z]+)->/", $item['variant'], $key_parts);

				$variant_keys = $key_parts[1];
				$variant_values = preg_split("/([a-z]+)->/", $item['variant'], 0, PREG_SPLIT_NO_EMPTY);
				$count = count($variant_keys);

				$variant_descriptions = array();

				for($index = 0; $index < $count; $index++) {
					$key = $variant_keys[$index];
					$value = trim(str_replace('+', ' ', $variant_values[$index]));
					$variant_descriptions[] = "$key: $value";
				}

				$item['variant'] = implode(', ', $variant_descriptions);
			}
		}

		$order_details['padded_id'] = str_pad($order_details['id'],6,0,STR_PAD_LEFT);
		$order_details['order_date'] = date("M j, Y, g:i A", $order_details['creation_date']);
		$order_details['formatted_gross_price'] = sprintf("%01.2f",$order_details['gross_price']);
		if ($order_details['gross_price']-$item_price) {
			$order_details['formatted_shipping'] = number_format($order_details['gross_price']-$item_price,2);
		}
		$order_details['formatted_net_price'] = sprintf("%01.2f",$order_details['gross_price'] - $order_details['service_fee']);
		$order_details['order_connection_details'] = AdminHelper::getConnectionName($order_details['connection_id']) . ' (' . $order_details['connection_type'] . ')';
		//if ($order_details['fulfilled']) { $order_details['order_fulfilled'] = 'yes'; } else { $order_details['order_fulfilled'] = 'no'; }
		$cash_admin->page_data = array_merge($cash_admin->page_data,$order_details);
		$cash_admin->page_data['order_contents'] = new ArrayIterator($order_contents);
		$cash_admin->page_data['customer_display_name'] = $order_details['customer_details']['display_name'];
		$cash_admin->page_data['customer_email_address'] = $order_details['customer_details']['email_address'];
		$cash_admin->page_data['customer_address_country'] = $order_details['customer_details']['address_country'];
		$cash_admin->page_data['shipping_name'] = $order_details['customer_shipping_name'];
		$cash_admin->page_data['shipping_email'] = $order_details['customer_email'];
		$cash_admin->page_data['shipping_address1'] = $order_details['customer_address1'];
		$cash_admin->page_data['shipping_address2'] = $order_details['customer_address2'];
		$cash_admin->page_data['shipping_city'] = $order_details['customer_city'];
		$cash_admin->page_data['shipping_region'] = $order_details['customer_region'];
		$cash_admin->page_data['shipping_postalcode'] = $order_details['customer_postalcode'];
		$cash_admin->page_data['shipping_country'] = $order_details['customer_country'];
		$cash_admin->page_data['notes'] = $order_details['notes'];
		$cash_admin->page_data['ui_title'] = 'Order #' . $order_details['padded_id'];

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
