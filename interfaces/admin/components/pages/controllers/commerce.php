<?php
/*******************************************************************************
 *
 * 1. SET UP SCRIPT VARIABLES
 *
 ******************************************************************************/
$page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));

/*******************************************************************************
 *
 * 2. HANDLE ORDERS MARKED AS FULFILLED
 *
 ******************************************************************************/
if (isset($_POST['fulfill'])) {
	$order_details_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'commerce',
			'cash_action' => 'editorder',
			'id' => $_POST['fulfill'],
			'fulfilled' => 1
		)
	);
	if ($request_parameters) {
		$addtourl = implode('/',$request_parameters);
	} else {
		$addtourl = '';
	}
}

/*******************************************************************************
 *
 * 3. SECTION SETTINGS
 *
 ******************************************************************************/
if (isset($_POST['currency_id'])) {
	$settings_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system',
			'cash_action' => 'setsettings',
			'type' => 'use_currency',
			'value' => $_POST['currency_id'],
			'user_id' => $cash_admin->effective_user_id
		)
	);
	$settings_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system',
			'cash_action' => 'setsettings',
			'type' => 'payment_defaults',
			'value' => array(
				'pp_default' => $_POST['paypal_default_id'],
				'pp_micro' => $_POST['paypal_micropayment_id']
			),
			'user_id' => $cash_admin->effective_user_id
		)
	);
	if ($settings_response['payload']) {
		AdminHelper::formSuccess('Success.','/commerce/');
	}
}
// now get the current currency setting
$settings_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'system',
		'cash_action' => 'getsettings',
		'type' => 'use_currency',
		'user_id' => $cash_admin->effective_user_id
	)
);
if ($settings_response['payload']) {
	$current_currency = $settings_response['payload'];
} else {
	$current_currency = 'USD';
}
$cash_admin->page_data['currency_options'] = AdminHelper::echoCurrencyOptions($current_currency);
// current paypal
$settings_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'system',
		'cash_action' => 'getsettings',
		'type' => 'payment_defaults',
		'user_id' => $cash_admin->effective_user_id
	)
);
if (is_array($settings_response['payload'])) {
	$pp_default = $settings_response['payload']['pp_default'];
	$pp_micro = $settings_response['payload']['pp_micro'];
} else {
	$pp_default = 0;
	$pp_micro = 0;
}
$cash_admin->page_data['currency_options'] = AdminHelper::echoCurrencyOptions($current_currency);

$pp = array();
foreach ($page_data_object->getConnectionsByType('com.paypal') as $ppq) {
	$pp[$ppq['id']] = $ppq['name'];
}
$cash_admin->page_data['paypal_default_options'] = AdminHelper::echoFormOptions($pp,$pp_default,false,true);
$cash_admin->page_data['paypal_micro_options'] = AdminHelper::echoFormOptions($pp,$pp_micro,false,true);


// handle regions
if (isset($_POST['region1'])) {
	$regions = array(
		'region1' => $_POST['region1'],
		'region2' => $_POST['region2']
	);
	$settings_response = $cash_admin->requestAndStore(
		array(
			'cash_request_type' => 'system',
			'cash_action' => 'setsettings',
			'type' => 'regions',
			'value' => $regions,
			'user_id' => $cash_admin->effective_user_id
		)
	);
	if ($settings_response['payload']) {
		AdminHelper::formSuccess('Success.','/commerce/');
	}
}
// now get the current setting
$settings_response = $cash_admin->requestAndStore(
	array(
		'cash_request_type' => 'system',
		'cash_action' => 'getsettings',
		'type' => 'regions',
		'user_id' => $cash_admin->effective_user_id
	)
);
if ($settings_response['payload']) {
	$cash_admin->page_data['region1'] = $settings_response['payload']['region1'];
	$cash_admin->page_data['region2'] = $settings_response['payload']['region2'];
} else {
	$cash_admin->page_data['noshippingregions'] = true;
}

/*******************************************************************************
 *
 * 4. PAGE VIEW FILTERS, GET ORDERS FOR SPECIFIED VIEW
 *
 ******************************************************************************/
$cash_admin->page_data['current_page'] = 1;
$cash_admin->page_data['next_page'] = 2;
$cash_admin->page_data['show_previous'] = false;
$filter = false;

$cash_admin->page_data['no_filter'] = true;
if ($request_parameters) {
	$filter_key = array_search('filter', $request_parameters);
	if ($filter_key !== false) {
		$filter = $request_parameters[$filter_key + 1];
		$cash_admin->page_data['no_filter'] = false;
		$cash_admin->page_data['filter_type'] = $filter;
		if ($filter == 'week') {
			$cash_admin->page_data['filter_week'] = true;
		} else if ($filter == 'all') {
			$cash_admin->page_data['filter_all'] = true;
		}
	}

	$page_key = array_search('page', $request_parameters);
	if ($page_key !== false) {
		$cash_admin->page_data['current_page'] = $request_parameters[$page_key + 1];
		$cash_admin->page_data['next_page'] = $request_parameters[$page_key + 1] + 1;
		$cash_admin->page_data['previous_page'] = $request_parameters[$page_key + 1] - 1;
		if ($cash_admin->page_data['previous_page'] == 1) {
			$cash_admin->page_data['back_to_first'] = true;
		}
		$cash_admin->page_data['show_pagination'] = true;
		$cash_admin->page_data['show_previous'] = true;
	}
} else {
	$cash_admin->page_data['no_filter'] = true;
}

$order_request = array(
	'cash_request_type' => 'commerce',
	'cash_action' => 'getordersforuser',
	'user_id' => $cash_admin->effective_user_id,
	'max_returned' => 11,
	'skip' => ($cash_admin->page_data['current_page'] - 1) * 10,
	'deep' => true
);
if ($cash_admin->page_data['no_filter']) {
	$order_request['unfulfilled_only'] = 1;
}
if ($filter == 'week') {
	$order_request['since_date'] = time() - 604800;
}
if ($filter == 'byitem') {
	$order_request['cash_action'] = 'getordersbyitem';
	$order_request['item_id'] = $request_parameters[$filter_key + 2];
	$cash_admin->page_data['filter_item_id'] = $order_request['item_id'];
}

$orders_response = $cash_admin->requestAndStore($order_request);

/*******************************************************************************
 *
 * 5. GET ALL VALID SERVICE CONNECTIONS FOR FIRST-USE
 *
 ******************************************************************************/
$cash_admin->page_data['connection'] = AdminHelper::getConnectionsByScope('commerce');

if (!$cash_admin->page_data['connection']) {
	if (!is_array($orders_response['payload'])) {
		$cash_admin->page_data['firstuse'] = true;
		$settings_types_data = $page_data_object->getConnectionTypes('commerce');

		$all_services = array();
		$typecount = 1;
		foreach ($settings_types_data as $key => $data) {
			if ($typecount % 2 == 0) {
				$alternating_type = true;
			} else {
				$alternating_type = false;
			}
			if (file_exists(ADMIN_BASE_PATH.'/assets/images/settings/' . $key . '.png')) {
				$service_has_image = true;
			} else {
				$service_has_image = false;
			}
			if (in_array($cash_admin->platform_type, $data['compatibility'])) {
				$all_services[] = array(
					'key' => $key,
					'name' => $data['name'],
					'description' => $data['description'],
					'link' => $data['link'],
					'alternating_type' => $alternating_type,
					'service_has_image' => $service_has_image
				);
				$typecount++;
			}
		}
		$cash_admin->page_data['all_services'] = new ArrayIterator($all_services);
	}
}

/*******************************************************************************
 *
 * 6. CLEAN UP ORDERS
 *
 ******************************************************************************/
if (is_array($orders_response['payload'])) {
	$all_order_details = array();
	foreach ($orders_response['payload'] as $o) {
		if ($o['successful']) {
			$order_date = $o['creation_date'];

			$order_contents = json_decode($o['order_contents'],true);
			$item_price = 0;
			foreach ($order_contents as $key => &$item) {
				if (!isset($item['qty'])) {
					$item['qty'] = 1;
				}
				$item_price += $item['qty'] * $item['price'];

				if (isset($item['variant'])) {
					$variant_response = $cash_admin->requestAndStore(
						array(
							'cash_request_type' => 'commerce',
							'cash_action' => 'formatvariantname',
							'name' => $item['variant']
						)
					);
					if ($variant_response['payload']) {
						$item['variant'] = $variant_response['payload'];
					}
				}
			}

			if ($o['gross_price'] - $item_price) {
				$shipping_cost = CASHSystem::getCurrencySymbol($o['currency']) . number_format($o['gross_price'] - $item_price,2);
				$item_price = CASHSystem::getCurrencySymbol($o['currency']) . number_format($item_price,2);
			} else {
				$shipping_cost = false;
			}

			$customer_name = $o['customer_shipping_name'];
			if (!$customer_name) {
				$customer_name = $o['customer_name'];
			}

			$all_order_details[] = array(
				'id' => $o['id'],
				'customer_name' => $customer_name,
				'customer_email' => $o['customer_email'],
				'customer_address1' => $o['customer_address1'],
				'customer_address2' => $o['customer_address2'],
				'customer_city' => $o['customer_city'],
				'customer_region' => $o['customer_region'],
				'customer_postalcode' => $o['customer_postalcode'],
				'customer_country' => $o['customer_country'],
				'number' => '#' . str_pad($o['id'],6,0,STR_PAD_LEFT),
				'date' => CASHSystem::formatTimeAgo((int)$order_date,true),
				'order_description' => str_replace("\n",' ',$o['order_description']),
				'order_contents' => new ArrayIterator($order_contents),
				'shipping' => $shipping_cost,
				'itemtotal' => $item_price,
				'gross' => CASHSystem::getCurrencySymbol($o['currency']) . number_format($o['gross_price'],2),
				'fulfilled' => $o['fulfilled'],
				'notes' => $o['notes']
			);
		}
	}

	if (count($all_order_details) > 0) {
		if (count($all_order_details) > 10) {
			$cash_admin->page_data['show_pagination'] = true;
			$cash_admin->page_data['show_next'] = true;
			if ($cash_admin->page_data['show_previous']) {
				$cash_admin->page_data['show_nextandprevious'] = true;
			}
			array_pop($all_order_details);
		}
		$cash_admin->page_data['orders_recent'] = new ArrayIterator($all_order_details);
		$cash_admin->page_data['show_filters'] = true;
	}
}



/*******************************************************************************
 *
 * 7. SET THE TEMPLATE AND GO!
 *
 ******************************************************************************/
$cash_admin->setPageContentTemplate('commerce');
?>
