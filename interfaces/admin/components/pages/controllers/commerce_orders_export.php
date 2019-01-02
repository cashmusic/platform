<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

/**
 * @param $order_totals_description
 * @return string
 */
function formatColumn($column)
{
    return ',"' . str_replace('"', '""', $column) . '"';
}

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
		echo '"order id","order date","description","shipping name","email address","first name","last name","address 1","address 2","city","region","postal code","country code","country","gross price","service fee","total shipping"' . "\n";

		if ($orders_response['status_uid'] == 'commerce_getordersforuser_200' && is_array($orders_response['payload'])) {
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
					$order_totals_description = '';
					$shipping_charged = 0;
					$order_response = $cash_admin->requestAndStore(
						array(
							'cash_request_type' => 'commerce',
							'cash_action' => 'getordertotals',
							'order_contents' => $entry['order_contents']
						)
					);
					if ($order_response['payload']) {
						$order_totals_description = $order_response['payload']['description'];
						if ($order_response['payload']['price']) {
							$shipping_charged = $entry['gross_price'] - $order_response['payload']['price'];
						}
					}
					// end TODO
                    $customer_shipping_name = isset($entry['customer_shipping_name']) ? $entry['customer_shipping_name'] : "";
                    $customer_email = isset($entry['customer_email']) ? $entry['customer_email'] : "";
                    $customer_first_name = isset($entry['customer_first_name']) ? $entry['customer_first_name'] : "";
                    $customer_last_name = isset($entry['customer_last_name']) ? $entry['customer_last_name'] : "";
                    $customer_address = isset($entry['customer_address1']) ? $entry['customer_address1'] : "";
                    $customer_address1 = isset($entry['customer_address2']) ? $entry['customer_address2'] : "";
                    $customer_city = isset($entry['customer_city']) ? $entry['customer_city'] : "";
                    $customer_region = isset($entry['customer_region']) ? $entry['customer_region'] : "";
                    $customer_postalcode = isset($entry['customer_postalcode']) ? $entry['customer_postalcode'] : "";
                    $customer_countrycode = isset($entry['customer_countrycode']) ? $entry['customer_countrycode'] : "";
                    $customer_country = isset($entry['customer_country']) ? $entry['customer_country'] : "";
                    $gross_price = isset($entry['gross_price']) ? $entry['gross_price'] : "";
                    $service_fee = isset($entry['service_fee']) ? $entry['service_fee'] : "";

				   	echo '"' . str_replace ('"','""',$entry['id']) . '"';
					echo formatColumn(date('M j, Y h:iA T',$entry['creation_date']));
					//echo ',"' . str_replace ('"','""',$entry['transaction_description']) . '"';
					echo formatColumn($order_totals_description);
                    echo formatColumn($customer_shipping_name);
                    echo formatColumn($customer_email);
                    echo formatColumn($customer_first_name);
                    echo formatColumn($customer_last_name);
                    echo formatColumn($customer_address);
                    echo formatColumn($customer_address1);
                    echo formatColumn($customer_city);
                    echo formatColumn($customer_region);
                    echo formatColumn($customer_postalcode);
                    echo formatColumn($customer_countrycode);
                    echo formatColumn($customer_country);
                    echo formatColumn($gross_price);
                    echo formatColumn($service_fee);
					echo formatColumn(number_format($shipping_charged,2));
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
