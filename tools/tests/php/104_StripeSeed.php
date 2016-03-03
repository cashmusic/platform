<?php

require_once(dirname(__FILE__) . '/base.php');

require CASH_PLATFORM_ROOT . '/lib/paypal/autoload.php';

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Refund;
use PayPal\Api\RefundDetail;
use PayPal\Api\Sale;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;


class StripeSeedTests extends UnitTestCase {
	protected $client_id, $client_secret, $error_message, $transaction_id, $cash_user_id, $stripe_connection_id, $transaction_request;
	public $publishable_key, $redirects;
	
	function __construct() {
		echo "Testing Stripe Seed\n";
		
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
		

		$c = new CASHConnection($this->cash_user_id); // the '1' sets a user id=1
		$this->stripe_connection_id = $c->setSettings('Stripe', 'com.stripe',
			array(
				"client_id" => getTestEnv("STRIPE_client_id"),
  				"client_secret" =>  getTestEnv("STRIPE_client_secret"),
  				"publishable_key" => getTestEnv("STRIPE_publishable_key")
			) 
		);

		$this->transaction_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'addtransaction',
				'user_id' => $this->cash_user_id,
				'connection_id' => 1,
				'connection_type' => 'com.stripe',
				'service_timestamp' => 'string not int — different formats',
				'service_transaction_id' => '123abc',
				'data_sent' => 'false',
				'data_returned' => 'false',
				'successful' => -1,
				'gross_price' => 123.45,
				'service_fee' => 12.34
			)
		);

		$this->redirects = false;

		$session_request = new CASHRequest(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'startjssession'
			)
		);
		if ($session_request->response['payload']) {
			$s = json_decode($session_request->response['payload'],true);
			$this->session_id = $s['id'];
		}

		$this->testing_transaction = $this->transaction_request->response['payload'];
	}

	function testStripeSeed(){

		$payment_seed = new StripeSeed($this->cash_user_id, $this->stripe_connection_id);
		$this->assertIsa($payment_seed, 'StripeSeed');
	}
/*
	function testSingleItemNoShipping(){
		if($this->paypal_account) {
			$payment_seed = new PaypalSeed($this->cash_user_id, $this->paypal_connection_id);

			$payment_details = $payment_seed->preparePayment(
				'6.66',										# payment amount
				'order-sku',								# order id
				'the order of the beast',					# order name
				'http://dev.localhost:8888?cash_request_type=commerce&cash_action=finalizepayment',				# return URL
				'http://dev.localhost:8888?cash_request_type=commerce&cash_action=finalizepayment',				# cancel URL (the same in our case)
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

			$payment_details = $payment_seed->preparePayment(
				6.66,										# payment amount
				'order-sku',								# order id
				'the order of the beast',					# order name
				'http://dev.localhost:8888?cash_request_type=commerce&cash_action=finalizepayment',				# return URL
				'http://dev.localhost:8888?cash_request_type=commerce&cash_action=finalizepaymentt',				# cancel URL (the same in our case)
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

	function testSandboxOff() {

	}

	function testSandboxOn() {

	}*/
}
