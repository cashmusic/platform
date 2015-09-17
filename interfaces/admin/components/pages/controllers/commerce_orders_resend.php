<?php
$cash_admin->page_data['ui_title'] = '';
$cash_admin->page_data['id'] = $request_parameters[0];

$order_details = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'commerce',
		'cash_action' => 'getorder',
		'id' => $request_parameters[0]
	)
);

$analytics = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'element',
		'cash_action' => 'getanalytics',
		'analtyics_type' => 'elementbasics',
		'element_id' => $order_details['payload']['element_id'],
		'user_id' => $cash_admin->effective_user_id
	)
);

if (is_array($analytics['payload'])) {
	$cash_admin->page_data['total_views'] = $analytics['payload']['total'];

	$tmp_locations_array = array(); // temp array to combine totals by hostname
	foreach ($analytics['payload']['locations'] as $location => $total) {
		// cycle through all locations, push to temp array and combine if necessary
		$parsed = parse_url($location);
		// fix when &access_token is set without an initial ? query
		$better_path = explode('&access_token', $parsed['path']);
		$path = $better_path[0];
		if (isset($tmp_locations_array[$parsed['scheme'] . '://' . $parsed['host'] . $path])) {
			$tmp_locations_array[$parsed['scheme'] . '://' . $parsed['host'] . $path] = $tmp_locations_array[$parsed['scheme'] . '://' . $parsed['host'] . $path] + $total;
		} else {
			$tmp_locations_array[$parsed['scheme'] . '://' . $parsed['host'] . $path] = $total;
		}
	}
	arsort($tmp_locations_array); // sort temp array most to least

	$locations_array = array(); // let's rebuild the locations array
	foreach ($tmp_locations_array as $location => $total) {
		$locations_array[] = array(
			'access_location' => $location,
			'total' => $total
		);
	}

	$cash_admin->page_data['location_analytics'] = new ArrayIterator($locations_array);
}

$cash_admin->setPageContentTemplate('commerce_orders_resend');
?>
