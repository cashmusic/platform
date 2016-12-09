<?php

    $settings_request = new CASHRequest(
        array(
            'cash_request_type' => 'system',
            'cash_action' => 'getsettings',
            'type' => 'payment_defaults',
            'user_id' => $cash_admin->effective_user_id
        )
    );
    if (is_array($settings_request->response['payload'])) {
        $stripe_default = (isset($settings_request->response['payload']['stripe_default'])) ? $settings_request->response['payload']['stripe_default'] : false;
    }

if (!empty($_POST['action']) && $_POST['action'] == "do_create") {

    // add plan
    $subscription_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'createsubscriptionplan',
            'user_id' => $cash_admin->effective_user_id,
            'connection_id' => $stripe_default,
            'plan_name' => $_POST['name'],
            'description' => $_POST['description'],
            'sku' => "cash_".$cash_admin->effective_user_id."_".uniqid(),
            'amount' => $_POST['price'],
            'flexible_price' => (isset($_POST['flexible_price'])) ? true : false,
            'recurring' => (isset($_POST['recurring'])) ? true : false,
            'suggested_price' => (isset($_POST['suggested_price'])) ? $_POST['suggested_price'] : 0,
            'physical' => (isset($_POST['physical'])) ? true : false,

            'interval' => (isset($_POST['interval'])) ? $_POST['interval'] : "month",
            'interval_count' => 12,
            'currency' => 'usd'
        )
    );

    error_log(
        print_r($subscription_request->response, true)
    );

    if ($subscription_request->response['payload']) {

        AdminHelper::formSuccess('Success. Subscription plan added.','/commerce/subscriptions/detail/'.$subscription_request->response['payload']['numeric_id']);
        /*CASHSystem::redirectToUrl(CASH_ADMIN_URL."/commerce/subscriptions/detail/".$subscription_request->response['payload']['numeric_id']);*/
    } else {
        AdminHelper::formFailure('Error. Something just didn\'t work right.',"/commerce/subscriptions/detail/".$subscription_request->response['payload']);
    }
} else {
    $cash_admin->setPageContentTemplate('commerce_subscriptions_add');
}



    ?>