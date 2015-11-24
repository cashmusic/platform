<?php

require_once(dirname(__FILE__) . '/base.php');

class PaypalSeedTests extends UnitTestCase {
	private $paypal_connection_id, $paypal_username,$cash_user_id;
	
	function __construct() {
		echo "Testing Paypal Seed\n";
		
		// add a new admin user for this
		$user_add_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'addlogin',
				'address' => 'email@anothertest.com',
				'password' => 'thiswillneverbeused',
				'is_admin' => 1
			)
		);
		$this->cash_user_id = $user_add_request->response['payload'];
		
		// add a new connection


		$this->paypal_account = getTestEnv("PAYPAL_ACCOUNT");
		if(!$this->paypal_account) {
			echo "Paypal credentials not found, skipping tests\n";
		}
		$c = new CASHConnection($this->cash_user_id); // the '1' sets a user id=1
		$this->paypal_connection_id = $c->setSettings('Paypal', 'com.paypal',
			array(
				"account" => $this->paypal_account,
				"client_id" => getTestEnv("PAYPAL_CLIENT_ID"),
				"secret" => getTestEnv("PAYPAL_SECRET"),
				"sandboxed" => true
			) 
		);
	}

	function testPaypalSeed(){
		if($this->paypal_account) {
			$pp = new PaypalSeed($this->cash_user_id, $this->paypal_connection_id);
			$this->assertIsa($pp, 'PaypalSeed');
		}
	}

	function testSingleItemNoShipping(){
		if($this->paypal_account) {
			$payment_seed = new PaypalSeed($this->cash_user_id, $this->paypal_connection_id);

			$payment_details = $payment_seed->setCheckout(
				'6.66',										# payment amount
				'order-sku',								# order id
				'the order of the beast',					# order name
				'http://dev.localhost:8888',				# return URL
				'http://dev.localhost:8888',				# cancel URL (the same in our case)
				false,										# shipping info required (boolean)
				false,										# allow an order note (boolean)
				'USD',										# payment currency
				'sale',										# transaction type (e.g. 'Sale', 'Order', or 'Authorization')
				false,										# invoice (boolean)
				0											# price additions (like shipping, but could be taxes in future as well)
			);

			$this->assertTrue($payment_details['redirect_url']);
			//$redirect = CASHSystem::redirectToUrl($redirect_url);
			//echo $redirect;
		}
	}

	function testSingleItemWithShipping(){
		if($this->paypal_account) {
			$payment_seed = new PaypalSeed($this->cash_user_id, $this->paypal_connection_id);

			$payment_details = $payment_seed->setCheckout(
				6.66,										# payment amount
				'order-sku',								# order id
				'the order of the beast',					# order name
				'http://dev.localhost:8888',				# return URL
				'http://dev.localhost:8888',				# cancel URL (the same in our case)
				true,										# shipping info required (boolean)
				true,										# allow an order note (boolean)
				'USD',										# payment currency
				'sale',										# transaction type (e.g. 'Sale', 'Order', or 'Authorization')
				false,										# invoice (boolean)
				1.23											# price additions (like shipping, but could be taxes in future as well)
			);

			$this->assertTrue($payment_details['redirect_url']);
			//$redirect = CASHSystem::redirectToUrl($redirect_url);
			//echo $redirect;
		}
	}
}
