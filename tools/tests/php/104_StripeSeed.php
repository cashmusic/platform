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
		$this->has_key = getTestEnv("STRIPE_TESTSECRET");
		if (empty($this->has_key)) $this->has_key = false;



		if ($this->has_key) {

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
            \Stripe\Stripe::setApiKey(getTestEnv("STRIPE_TESTSECRET"));
			$this->payment_seed = new StripeSeed($this->cash_user_id, $this->stripe_connection_id);
			$this->assertIsa($this->payment_seed, 'StripeSeed');


		}
	}

	/*function testDoPaymentNoToken(){

			$payment_details = $this->payment_seed->doPayment(
				15.32, // total price
				"test transaction", // description
				false,            // token
				"timothy@mctest.com",    // email
				"tim mctest",    // name
				false);    // shipping info

			$this->assertFalse($payment_details);

			//$this->assertIsA($payment_seed, "array");
	}

	function testDoPaymentToken(){



			$token = \Stripe\Token::create(array(
				"card" => array(
					"number" => "4242424242424242",
					"exp_month" => 3,
					"exp_year" => 2017,
					"cvc" => "314"
				)
			));

			$payment_details = $this->payment_seed->doPayment(
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

	function testDoPaymentZeroCharge(){

			$token = \Stripe\Token::create(array(
				"card" => array(
					"number" => "4242424242424242",
					"exp_month" => 3,
					"exp_year" => 2017,
					"cvc" => "314"
				)
			));

			$payment_details = $this->payment_seed->doPayment(
				0, // total price
				"test transaction", // description
				$token,            // token
				"timothy@mctest.com",    // email
				"tim mctest",    // name
				false);    // shipping info

			$this->assertFalse($payment_details);
			//$this->assertIsA($payment_seed, "array");
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
	}*/

    function testCreateSubscriptionPlan() {

        // we need to make sure we didn't get cut off during testing so these all will validate
        $this->payment_seed->deleteSubscriptionPlan("my-test-plan");
        $this->payment_seed->deleteSubscriptionPlan("my-awesome-plan");


        $plans = $this->payment_seed->getAllSubscriptionPlans();

        // there should be no plans existent
        $this->assertFalse(
            (count($plans['data']) > 0)
        );

        // create a plan
        $this->plan_id = $this->payment_seed->createSubscriptionPlan("My Test Plan", 100, "month");

        // verify that it was added to the server
        $plan_check = $this->payment_seed->getSubscriptionPlan($this->plan_id);

        $this->assertIsA($plan_check, 'Stripe\Plan');
        $this->assertEqual($plan_check->name, "My Test Plan");

    }

    function testUpdateSubscriptionPlan() {

        // update the plan
        $this->assertTrue(
            $this->payment_seed->updateSubscriptionPlan($this->plan_id, "My Test Plan (Changed)")
        );

        // load the plan again and assert the changes
        $plan_check = $this->payment_seed->getSubscriptionPlan($this->plan_id);

        $this->assertIsA($plan_check, 'Stripe\Plan');
        $this->assertEqual($plan_check->name, "My Test Plan (Changed)");
    }

    function testDeleteSubscriptionPlan() {
        // delete the subscription plan
        $this->assertTrue(
            $this->payment_seed->deleteSubscriptionPlan($this->plan_id)
        );

        // try to load the plan and assert it does not exist
        $this->assertFalse(
          $this->payment_seed->getSubscriptionPlan($this->plan_id)
        );
    }

    function testAddSubscription() {
        // create new plan again
        $this->plan_id = $this->payment_seed->createSubscriptionPlan("My Awesome Plan", 100, "month");

        // verify that it was added to the server
        $plan_check = $this->payment_seed->getSubscriptionPlan($this->plan_id);

        $this->assertIsA($plan_check, 'Stripe\Plan');
        $this->assertEqual($plan_check->name, "My Awesome Plan");

        // assert that it has no subscribers
        $subscribers = $this->payment_seed->getAllSubscriptionsForPlan($this->plan_id, 1);

        $this->assertFalse(
            (count($subscribers['data']) > 0)
        );

        // create a new subscription
        $token = \Stripe\Token::create(array(
            "card" => array(
                "number" => "4242424242424242",
                "exp_month" => 11,
                "exp_year" => 2017,
                "cvc" => "314"
            )
        ));

        $subscription = $this->payment_seed->createSubscription($token, $this->plan_id, "tom@tom.com", 1);
        // assert that the subscription exists
        $this->subscription_id = $subscription->id;

        $subscription = $this->payment_seed->getSubscription($this->subscription_id);

        $this->assertIsA($subscription, '\Stripe\Subscription');

        // assert subscription belongs to plan
        $this->assertEqual($this->plan_id, $subscription->plan->id);
    }

    function testUpdateSubscription() {
        // update subscription
        $this->payment_seed->updateSubscription($this->subscription_id, $this->plan_id, true, 5);

        // assert updates
        $subscription = $this->payment_seed->getSubscription($this->subscription_id);

        $this->assertEqual(5, $subscription->quantity);

    }

    function testCancelSubscription() {
        // load subscriber
        $subscription = $this->payment_seed->getSubscription($this->subscription_id);
        $this->assertIsA($subscription, '\Stripe\Subscription');

        // cancel subscription
        $subscription = $this->payment_seed->cancelSubscription($this->subscription_id);

        // assert cancelled
        $this->assertEqual("canceled", $subscription->status);

        // clean up
        $this->assertTrue(
            $this->payment_seed->deleteSubscriptionPlan($this->plan_id)
        );
    }


}
