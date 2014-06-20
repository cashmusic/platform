<?php
// first grab first param — so we can later search for 'unfulfilled'
$allorunfulfilled = $request_parameters[0];

// initial details for the request
$request_details = array(
	'cash_request_type' => 'commerce', 
	'cash_action' => 'getordersforuser',
	'user_id' => $cash_admin->effective_user_id,
	'deep' => 1
);

// add unfulfilled_only if it applies
if ($allorunfulfilled == 'unfulfilled') {
	$request_details['unfulfilled_only'] = 1;
} else {
	$allorunfulfilled = 'all';
}

// make the request:
$orders_response = $cash_admin->requestAndStore($request_details);

if (is_array($orders_response)) {
	header('Content-Disposition: attachment; filename="orders_' . $allorunfulfilled . '_' . date('Mj-Y',time()) . '.csv"');
	if ($orders_response['status_uid'] == 'commerce_getordersforuser_200') {
		echo '"order id","order date","description","shipping name","first name","last name","address 1","address 2","city","region","postal code","country code","country","gross price","service fee"' . "\n";
		
		foreach ($orders_response['payload'] as $entry) {
		    echo '"' . str_replace ('"','""',$entry['id']) . '"';
			echo ',"' . date('M j, Y h:iA T',$entry['modification_date']) . '"';
			echo ',"' . str_replace ('"','""',$entry['transaction_description']) . '"';
			echo ',"' . str_replace ('"','""',$entry['customer_shipping_name']) . '"';
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

			if ($allorunfulfilled == 'unfulfilled') {
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
	} else {
		echo "There are no matching orders.";
	}
}

exit;
?>