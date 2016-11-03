<?php
require_once(dirname(__FILE__) . '/base.php');
require_once(CASH_PLATFORM_ROOT.'/classes/plants/CommercePlant.php');

// we need the seeds
require_once(CASH_PLATFORM_ROOT.'/classes/seeds/StripeSeed.php');
require_once(CASH_PLATFORM_ROOT.'/classes/seeds/PaypalSeed.php');


class CommercePlantSubscriptionsTests extends UnitTestCase {
    var $testing_item,$testing_order,$testing_transaction, $session_id;

    function __construct()
    {
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

        $c = new CASHConnection($this->cash_user_id); // the '1' sets a user id=1
        $this->stripe_connection_id = $c->setSettings('Stripe', 'com.stripe',
            array(
                "client_secret" => getTestEnv("STRIPE_TESTSECRET"),
                "publishable_key" => getTestEnv("STRIPE_TESTPUBLISHABLE")
            )
        );
    }

    function testCreateSubscriptionPlan(){

        $subscription_request = new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'createsubscriptionplan',
                'user_id' => $this->cash_user_id,
                'connection_id' => $this->stripe_connection_id,
                'plan_name' => "Some Test Plan",
                'description' => "Description for xyz plan",
                'sku' => "cash_user_".$this->cash_user_id."_".uniqid(),
                'amount' => 1,
                'interval' => 'month',
                'interval_count' => 12
            )
        );

        $this->assertTrue($subscription_request->response['payload']);

        $this->plan_id = 1;

    }

    function testGetSubscriptionPlan(){
        $subscription_request = new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'getsubscriptionplan',
                'user_id' => $this->cash_user_id,
                'connection_id' => $this->stripe_connection_id,
                'id' => $this->plan_id
            )
        );

        $this->assertTrue($subscription_request->response['payload']);
    }

    function testUpdateSubscriptionPlan(){

    }

    function testCancelSubscriptionPlan(){

    }

    function testAddPlanSubscriber(){

    }

    function testGetPlanSubscribers(){

    }

    function testUpdatePlanSubscriber(){

    }

    function testEmailPlanSubscribers(){

    }

    function testGetSubscriptionsByCustomer(){

    }

    function testInitiateSubscription(){

    }

    function testFinalizeSubscription(){

    }



}

?>
