<?php
/***************************************************************************
 *
 *
 * Sample commerce data. Adds orders and connections (if needed) for testing
 * 
 * USAGE: command line. just `php commerce.php` should do the trick
 *        if you're running vagrant do a `vagrant ssh` first
 *
 *
 ***************************************************************************/


	// http://www.ivankristianto.com/php-snippet-code-to-generate-random-float-number/
	/**
	* Generate Float Random Number
	*
	* @param float $Min Minimal value
	* @param float $Max Maximal value
	* @param int $round The optional number of decimal digits to round to. default 0 means not round
	* @return float Random float value
	*/
	function float_rand($Min, $Max, $round=0) {
		//validate input
		if ($min > $Max) { $min=$Max; $max=$Min; }
		else { $min=$Min; $max=$Max; }
		$randomfloat = $min + mt_rand() / mt_getrandmax() * ($max - $min);
		if($round > 0) {
			$randomfloat = round($randomfloat,$round);
		}

		return $randomfloat;
	}

	// include the main CASH file
	include_once(__DIR__ . '/../../framework/cashmusic.php');

	// check for existing commerce connections
	// if not found, add a fake paypal connection for testing
	$page_data_object = new CASHConnection(1);
	$connections = $page_data_object->getConnectionsByScope('commerce');
	if (is_array($connections)) {
		$c_id = $connections[0]['id'];
	} else {
		$c = new CASHConnection(1);
		$c_id = $c->setSettings(
			'Fake Paypal connection',
			'com.paypal',
			array('whatever' => 0)
		);
	}

	// add a customer. if this fake dude's already in the system we just get his ID
	$cust = new CASHRequest(
		array(
			'cash_request_type' => 'system', 
			'cash_action' => 'addlogin',
			'address' => 'fake@buyer.com',
			'password' => 'whocares',
			'first_name' => 'Fake',
			'last_name' => 'McTest',
			'display_name' => 'Fake McTest'
		)
	);

	// do between one and seven orders. mix it up!
	$loops = rand(1,7);
	$i = 1;

	while($i<=$loops) {

		// randomize the price
		$price = float_rand(3.00,150.00,2);
		$fee = 0.05 + ($price * 0.03);

		// first add the transation
		$cr = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'addtransaction',
				'user_id' => 1,
				'connection_id' => $c_id,
				'connection_type' => 'com.paypal',
				'service_timestamp' => time(),
				'service_transaction_id' => '13b2513adb39d',
				'data_sent' => '{"TOKEN":"EC-3CC324611K842411K","CHECKOUTSTATUS":"PaymentActionNotInitiated","TIMESTAMP":"2014-10-02T19:16:19Z","CORRELATIONID":"eaa324c4ce9ge","ACK":"Success","VERSION":"63.0","BUILD":"3719653","EMAIL":"fake@buyer.com","PAYERID":"KW2MPO2RWV4SW","PAYERSTATUS":"verified","FIRSTNAME":"Fake","LASTNAME":"McTest","COUNTRYCODE":"US","CURRENCYCODE":"USD","AMT":"5.99","ITEMAMT":"5.99","SHIPPINGAMT":"0.00","HANDLINGAMT":"0.00","TAXAMT":"0.00","DESC":"Sample item","INSURANCEAMT":"0.00","SHIPDISCAMT":"0.00","L_NAME0":"Sample item","L_NUMBER0":"order-6","L_QTY0":"1","L_TAXAMT0":"0.00","L_AMT0":"5.99","L_ITEMWEIGHTVALUE0":"   0.00000","L_ITEMLENGTHVALUE0":"   0.00000","L_ITEMWIDTHVALUE0":"   0.00000","L_ITEMHEIGHTVALUE0":"   0.00000","PAYMENTREQUEST_0_CURRENCYCODE":"USD","PAYMENTREQUEST_0_AMT":"5.99","PAYMENTREQUEST_0_ITEMAMT":"5.99","PAYMENTREQUEST_0_SHIPPINGAMT":"0.00","PAYMENTREQUEST_0_HANDLINGAMT":"0.00","PAYMENTREQUEST_0_TAXAMT":"0.00","PAYMENTREQUEST_0_DESC":"Sample item","PAYMENTREQUEST_0_INSURANCEAMT":"0.00","PAYMENTREQUEST_0_SHIPDISCAMT":"0.00","PAYMENTREQUEST_0_INSURANCEOPTIONOFFERED":"false","L_PAYMENTREQUEST_0_NAME0":"Sample item","L_PAYMENTREQUEST_0_NUMBER0":"order-6","L_PAYMENTREQUEST_0_QTY0":"1","L_PAYMENTREQUEST_0_TAXAMT0":"0.00","L_PAYMENTREQUEST_0_AMT0":"23.00","L_PAYMENTREQUEST_0_ITEMWEIGHTVALUE0":"   0.00000","L_PAYMENTREQUEST_0_ITEMLENGTHVALUE0":"   0.00000","L_PAYMENTREQUEST_0_ITEMWIDTHVALUE0":"   0.00000","L_PAYMENTREQUEST_0_ITEMHEIGHTVALUE0":"   0.00000"}',
				'data_returned' => '{"TOKEN":"EC-3CC324611K842411K","SUCCESSPAGEREDIRECTREQUESTED":"false","TIMESTAMP":"2014-10-02T19:16:26Z","CORRELATIONID":"11a6663cxo30d","ACK":"Success","VERSION":"63.0","BUILD":"3719653","INSURANCEOPTIONSELECTED":"false","SHIPPINGOPTIONISDEFAULT":"false","PAYMENTINFO_0_TRANSACTIONID":"4WN12370L1021013D","PAYMENTINFO_0_TRANSACTIONTYPE":"expresscheckout","PAYMENTINFO_0_PAYMENTTYPE":"instant","PAYMENTINFO_0_ORDERTIME":"2014-10-02T19:16:23Z","PAYMENTINFO_0_AMT":"5.99","PAYMENTINFO_0_FEEAMT":"0.35","PAYMENTINFO_0_TAXAMT":"0.00","PAYMENTINFO_0_CURRENCYCODE":"USD","PAYMENTINFO_0_PAYMENTSTATUS":"Completed","PAYMENTINFO_0_PENDINGREASON":"None","PAYMENTINFO_0_REASONCODE":"None","PAYMENTINFO_0_PROTECTIONELIGIBILITY":"Ineligible","PAYMENTINFO_0_ERRORCODE":"0","PAYMENTINFO_0_ACK":"Success"}',
				'successful' => 1,
				'gross_price' => $price,
				'service_fee' => $fee,
				'currency' => 'USD',
				'status' => 'complete'
			)
		);
		// id in $cr->response['payload']

		// now create a full order
		$ord = new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'addorder',
				'user_id' => 1,
				'customer_user_id' => $cust->response['payload'],
				'transaction_id' => $cr->response['payload'],
				'order_contents' => array(
					array(
						'id' => 1,
						'user_id' => 1,
						'name' => 'Sample item',
						'description' => 'This is a description for the test item.',
						'sku' => '#abc123',
						'price' => $price,
						'flexible_price' => 0,
						'digital_fulfillment' => 1,
						'physical_fulfillment' => 0,
						'physical_weight' => 0,
						'physical_width' => 0,
						'physical_height' => 0,
						'physical_depth' => 0,
						'available_units' => -1,
						'variable_pricing' => 0,
						'fulfillment_asset' => 1,
						'descriptive_asset' => 0,
						'creation_date' => time()
					)
				),
				'fulfilled' => 0,
				'canceled' => 0,
				'physical' => 0,
				'digital' => 1,
				'notes' => '',
				'country_code' => 'US',
				'currency' => 'USD',
				'element_id' => 1,
			)
		);

		// this is silly, but we need to edit the order to get a positive "modification_date"
		// in the table — easiest way to test for abandoned/non-abandoned. that sounds really
		// messed up, but it makes a lot of sense given that we need to get a response from 
		// any service .

		// TODO: randomize a chance that this order gets abandonned
		new CASHRequest(
			array(
				'cash_request_type' => 'commerce', 
				'cash_action' => 'editorder',
				'id' => $ord->response['payload'],
				'fulfilled' => 1,
			)
		);

		$i++;
	}

?>