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
		$this->paypal_username = getTestEnv("PAYPAL_USERNAME");
		if(!$this->paypal_username) {
			echo "Paypal credentials not found, skipping tests\n";
		}
		$c = new CASHConnection($this->cash_user_id); // the '1' sets a user id=1
		$this->paypal_connection_id = $c->setSettings('Paypal', 'com.paypal',
			array(
				"username" => $this->paypal_username, 
				"password" => getTestEnv("PAYPAL_PASSWORD"), 
				"signature" => getTestEnv("PAYPAL_SIGNATURE"), 
				"sandboxed" => true
			) 
		);
	}

	function testPaypalSeed(){
		if($this->paypal_username) {
			$pp = new PaypalSeed($this->cash_user_id, $this->paypal_connection_id);
			$this->assertIsa($pp, 'PaypalSeed');
		}
	}

	function testSet(){
		if($this->paypal_username) {
			$pp = new PaypalSeed($this->cash_user_id, $this->paypal_connection_id);
			$redirect_url = $pp->setExpressCheckout(
				'13.26',
				'order_sku',
				'this is the best order ever',
				'http://localhost',
				'http://localhost'
			);
			$this->assertTrue($redirect_url);
			//$redirect = CASHSystem::redirectToUrl($redirect_url);
			//echo $redirect;
		}
	}
}
