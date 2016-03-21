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

		$this->api_context = new \PayPal\Rest\ApiContext(
			new \PayPal\Auth\OAuthTokenCredential(
				getTestEnv("PAYPAL_CLIENT_ID"),        # ClientID
				getTestEnv("PAYPAL_SECRET")            # ClientSecret
			)
		);


		$this->api_context->setConfig(
			array("mode" => "sandbox")
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

		$this->transaction_request = new CASHRequest(
			array(
				'cash_request_type' => 'commerce',
				'cash_action' => 'addtransaction',
				'user_id' => $this->cash_user_id,
				'connection_id' => 1,
				'connection_type' => 'com.paypal',
				'service_timestamp' => 'string not int — different formats',
				'service_transaction_id' => '123abc',
				'data_sent' => 'false',
				'data_returned' => 'false',
				'successful' => -1,
				'gross_price' => 123.45,
				'service_fee' => 12.34
			)
		);

		$this->testing_transaction = $this->transaction_request->response['payload'];
	}

	function testPaypalSeed(){
		if($this->paypal_account) {
			$pp = new PaypalSeed($this->cash_user_id, $this->paypal_connection_id);
			$this->assertIsa($pp, 'PaypalSeed');
		}
	}

	function testPreparePaymentFailure	(){
		if($this->paypal_account) {
			$payment_seed = new PaypalSeed($this->cash_user_id, $this->paypal_connection_id);

			$payment_details = $payment_seed->preparePayment(
				'6.66',										# payment amount
				'order-sku',								# order id
				'the order of the beast',					# order desc
				'http://dev.localhost:8888?cash_request_type=commerce&cash_action=finalizepayment',				# return URL
				'http://dev.localhost:8888?cash_request_type=commerce&cash_action=finalizepayment',				# cancel URL
				'USD',										# payment currency
				'INVALID TRANSACTION TYPE'										# transaction type (e.g. 'Sale', 'Order', or 'Authorization')
			);

			$this->assertFalse($payment_details['redirect_url']);
			//$redirect = CASHSystem::redirectToUrl($redirect_url);
			//echo $redirect;
		}
	}

	function testSinglePurchaseSuccess(){
		if($this->paypal_account) {
			$payment_seed = new PaypalSeed($this->cash_user_id, $this->paypal_connection_id);

			$payment_details = $payment_seed->preparePayment(
				'6.66',										# payment amount
				'order-sku',								# order id
				'the order of the beast',					# order desc
				'http://dev.localhost:8888?cash_request_type=commerce&cash_action=finalizepayment',				# return URL
				'http://dev.localhost:8888?cash_request_type=commerce&cash_action=finalizepayment',				# cancel URL
				'USD',										# payment currency
				'sale'										# transaction type (e.g. 'Sale', 'Order', or 'Authorization')
			);

			$this->assertIsA($payment_details['redirect_url'],"string");
			//$redirect = CASHSystem::redirectToUrl($redirect_url);
			//echo $redirect;
		}
	}

}
