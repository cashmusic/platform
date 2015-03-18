<?php
	if (isset($_POST['query'])) {
		$cash_admin->page_data['query'] = $_POST['query'];
		if(filter_var($_POST['query'], FILTER_VALIDATE_EMAIL)) {
			$order_response = $cash_admin->requestAndStore(
				array(
					'cash_request_type' => 'commerce', 
					'cash_action' => 'getordersbycustomer',
					'user_id' => $cash_admin->effective_user_id,
					'customer_email' => $_POST['query']
				)
			);

			// lists
			if (is_array($order_response['payload'])) {
				foreach ($order_response['payload'] as &$order) {
					$order['formatted_number'] = '#' . str_pad($order['id'],6,0,STR_PAD_LEFT);
				}
				$cash_admin->page_data['results'] = new ArrayIterator($order_response['payload']);
			}
		}
	}

	$cash_admin->setPageContentTemplate('search_results');
?>