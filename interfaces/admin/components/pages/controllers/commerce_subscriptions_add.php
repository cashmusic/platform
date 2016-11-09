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
            'physical' => (isset($_POST['physical'])) ? true : false,
            'interval' => $_POST['interval'],
            'interval_count' => 12,
            'currency' => 'usd'
        )
    );


    if ($subscription_request->response['payload']) {

        $subscription_request = new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'getsubscriptionplanbysku',
                'user_id' => $cash_admin->effective_user_id,
                'sku' => $subscription_request->response['payload']['id']
            )
        );

        $cash_admin->page_data['plan'] = $subscription_request->response['payload'];
        $cash_admin->setPageContentTemplate('commerce_subscriptions_detail');
    } else {
        echo "error";
    }
} else {
    $cash_admin->setPageContentTemplate('commerce_subscriptions_add');
}



    ?>