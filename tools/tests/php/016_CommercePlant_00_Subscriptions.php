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

        $this->has_key = getTestEnv("STRIPE_client_secret");
        if (empty($this->has_key)) $this->has_key = false;

        $this->commerce_plant = new CommercePlant('commerce', array());
    }

    function testCreateSubscriptionPlan(){

    }

    function testGetSubscriptionPlan(){

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
