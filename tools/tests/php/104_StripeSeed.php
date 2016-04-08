<?php

require_once(dirname(__FILE__) . '/base.php');

//require_once(CASH_PLATFORM_ROOT . '/lib/stripe/StripeOAuth.class.php');
//require_once(CASH_PLATFORM_ROOT . '/lib/stripe/init.php');

use \Stripe\Stripe;


class StripeSeedTests extends UnitTestCase {
	protected $client_id, $client_secret, $error_message, $transaction_id, $cash_user_id, $stripe_connection_id, $transaction_request;
	public $publishable_key, $redirects;
	
	function __construct() {
		echo "Testing Stripe Seed\n";
		//echo print_r(get_included_files(), true);
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
		$this->has_key = getTestEnv("STRIPE_client_secret");
		if (empty($this->has_key)) $this->has_key = false;



		if ($this->has_key) {
			$c = new CASHConnection($this->cash_user_id); // the '1' sets a user id=1
			$this->stripe_connection_id = $c->setSettings('Stripe', 'com.stripe',
				array(
					"client_id" => getTestEnv("STRIPE_client_id"),
					"client_secret" => getTestEnv("STRIPE_client_secret"),
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
				$s = json_decode($session_request->response['payload'], true);
				$this->session_id = $s['id'];
			}

			$this->testing_transaction = $this->transaction_request->response['payload'];

		}
	}

	function testStripeSeed(){
		if ($this->has_key) {

			$payment_seed = new StripeSeed($this->cash_user_id, $this->stripe_connection_id);
			$this->assertIsa($payment_seed, 'StripeSeed');

			\Stripe\Stripe::setApiKey(getTestEnv("STRIPE_client_secret"));
		}
	}

	function testDoPaymentNoToken(){
		if ($this->has_key) {

			$payment_seed = new StripeSeed($this->cash_user_id, $this->stripe_connection_id);
			$payment_details = $payment_seed->doPayment(
				15.32, // total price
				"test transaction", // description
				false,            // token
				"timothy@mctest.com",    // email
				"tim mctest",    // name
				false);    // shipping info

			$this->assertFalse($payment_details);
		}
			//$this->assertIsA($payment_seed, "array");
	}

	function testDoPaymentToken(){

		if ($this->has_key) {

			$payment_seed = new StripeSeed($this->cash_user_id, $this->stripe_connection_id);

			$token = \Stripe\Token::create(array(
				"card" => array(
					"number" => "4242424242424242",
					"exp_month" => 3,
					"exp_year" => 2017,
					"cvc" => "314"
				)
			));

			$payment_details = $payment_seed->doPayment(
				15.32, // total price
				"test transaction", // description
				$token,            // token
				"timothy@mctest.com",    // email
				"tim mctest",    // name
				false);    // shipping info

			$this->assertTrue($payment_details);
			$this->assertIsA($payment_details['transaction_id'], "string");
			$this->assertIsA($payment_details['payer'], "array");
			$this->assertEqual("timothy@mctest.com", $payment_details['payer']['email']);
			//$this->assertIsA($payment_seed, "array");
		}
	}

	function testDoPaymentZeroCharge(){

		if ($this->has_key) {

			$payment_seed = new StripeSeed($this->cash_user_id, $this->stripe_connection_id);

			$token = \Stripe\Token::create(array(
				"card" => array(
					"number" => "4242424242424242",
					"exp_month" => 3,
					"exp_year" => 2017,
					"cvc" => "314"
				)
			));

			$payment_details = $payment_seed->doPayment(
				0, // total price
				"test transaction", // description
				$token,            // token
				"timothy@mctest.com",    // email
				"tim mctest",    // name
				false);    // shipping info

			$this->assertFalse($payment_details);
			//$this->assertIsA($payment_seed, "array");
		}
	}

	function testDoPaymentChargeDeclined(){

		if ($this->has_key) {

		$payment_seed = new StripeSeed($this->cash_user_id, $this->stripe_connection_id);

		$token = \Stripe\Token::create(array(
			"card" => array(
				"number" => "4000000000000002",
				"exp_month" => 3,
				"exp_year" => 2017,
				"cvc" => "314"
			)
		));

		$payment_details = $payment_seed->doPayment(
			15, // total price
			"test transaction", // description
			$token,			// token
			"timothy@mctest.com",	// email
			"tim mctest",	// name
			false);	// shipping info

		$this->assertFalse($payment_details);
		//$this->assertIsA($payment_seed, "array");
		}
	}

	function testDoPaymentIncorrectCVC(){

		if ($this->has_key) {


		$payment_seed = new StripeSeed($this->cash_user_id, $this->stripe_connection_id);

		$token = \Stripe\Token::create(array(
			"card" => array(
				"number" => "4000000000000127",
				"exp_month" => 3,
				"exp_year" => 2017,
				"cvc" => "314"
			)
		));

		$payment_details = $payment_seed->doPayment(
			15, // total price
			"test transaction", // description
			$token,			// token
			"timothy@mctest.com",	// email
			"tim mctest",	// name
			false);	// shipping info

		$this->assertFalse($payment_details);
		//$this->assertIsA($payment_seed, "array");
	}
	}

}
