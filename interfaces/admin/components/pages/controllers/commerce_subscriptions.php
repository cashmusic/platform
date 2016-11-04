<pre>
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

/*    $subscription_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'createsubscriptionplan',
            'user_id' => $cash_admin->effective_user_id,
            'connection_id' => $stripe_default,
            'plan_name' => "Some Test Plan",
            'description' => "Description for xyz plan",
            'sku' => "cash_user_1_".uniqid(),
            'amount' => 1,
            'flexible_price' => false,
            'recurring' => true,
            'physical' => true,
            'interval' => 'month',
            'interval_count' => 12,
            'currency' => 'usd'
        )
    );

    if ($subscription_request->response['payload']) {
        //success
        echo "successfully added";
    }*/

    $subscription_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getsubscriptionplans',
            'user_id' => $cash_admin->effective_user_id,
            'limit' => 10
        )
    );

    if ($subscription_request->response['payload']) {

        var_dump($subscription_request->response['payload']);
    }

    ?>
</pre>