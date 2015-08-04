<?php
if (isset($_POST['export_options'])) {

	// initial details for the request
	$request_details = array(
		'cash_request_type' => 'commerce',
		'cash_action' => 'getordersforuser',
		'user_id' => $cash_admin->effective_user_id,
		'deep' => 1
	);

	if ($_POST['export_options'] == 'unfulfilled') {
		$request_details['unfulfilled_only'] = 1;
	}

	// make the request:
	$orders_response = $cash_admin->requestAndStore($request_details);

	if (is_array($orders_response)) {
		header('Content-Disposition: attachment; filename="orders_' . $_POST['export_options'] . '_' . date('Mj-Y',time()) . '.csv"');
		echo '"order id","order date","description","shipping name","email address","first name","last name","address 1","address 2","city","region","postal code","country code","country","gross price","service fee"' . "\n";

		if ($orders_response['status_uid'] == 'commerce_getordersforuser_200') {
			foreach ($orders_response['payload'] as $entry) {
				$go = true;
				if ($_POST['export_options'] == 'fulfilled') {
					if (!$entry['fulfilled']) {
						$go = false;
					}
				} elseif ($_POST['export_options'] == 'physical') {
					if (!$entry['physical']) {
						$go = false;
					}
				} elseif ($_POST['export_options'] == 'digital') {
					if ($entry['physical'] || !$entry['digital']) {
						$go = false;
					}
				}

				if ($go) {

					// TODO:
					// this is a temporary fix. yank it later
					$order_response = $cash_admin->requestAndStore(
						array(
							'cash_request_type' => 'commerce',
							'cash_action' => 'getordertotals',
							'order_contents' => $entry['order_contents']
						)
					);
					if ($order_response['payload']) {
						$order_totals_description = $order_response['payload']['description'];
					}
					// end TODO

				   echo '"' . str_replace ('"','""',$entry['id']) . '"';
					echo ',"' . date('M j, Y h:iA T',$entry['modification_date']) . '"';
					//echo ',"' . str_replace ('"','""',$entry['transaction_description']) . '"';
					echo ',"' . str_replace ('"','""',$order_totals_description) . '"';
					echo ',"' . str_replace ('"','""',$entry['customer_shipping_name']) . '"';
					echo ',"' . str_replace ('"','""',$entry['customer_email']) . '"';
					echo ',"' . str_replace ('"','""',$entry['customer_first_name']) . '"';
					echo ',"' . str_replace ('"','""',$entry['customer_last_name']) . '"';
					echo ',"' . str_replace ('"','""',$entry['customer_address1']) . '"';
					echo ',"' . str_replace ('"','""',$entry['customer_address2']) . '"';
					echo ',"' . str_replace ('"','""',$entry['customer_city']) . '"';
					echo ',"' . str_replace ('"','""',$entry['customer_region']) . '"';
					echo ',"' . str_replace ('"','""',$entry['customer_postalcode']) . '"';
					echo ',"' . str_replace ('"','""',$entry['customer_countrycode']) . '"';
					echo ',"' . str_replace ('"','""',$entry['customer_country']) . '"';
					echo ',"' . str_replace ('"','""',$entry['gross_price']) . '"';
					echo ',"' . str_replace ('"','""',$entry['service_fee']) . '"';
					echo "\n";
				}

				if (isset($_POST['mark_fulfilled']) && $go) {
					// mark as fulfilled
					$cash_admin->requestAndStore(
						array(
							'cash_request_type' => 'commerce',
							'cash_action' => 'editorder',
							'id' => $entry['id'],
							'fulfilled' => 1
						)
					);
				}
			}
		}
	}
}

exit;
?>
