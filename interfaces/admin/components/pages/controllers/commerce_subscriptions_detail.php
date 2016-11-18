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

    $plan_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getsubscriptionplan',
            'user_id' => $cash_admin->effective_user_id,
            'id' => $request_parameters[0]
        )
    );

    if ($plan_request->response['payload']) {

        $cash_admin->page_data['plan'] = $plan_request->response['payload'][0];
    }

    $subscription_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getallsubscriptionsbyplan',
            'id' => $request_parameters[0]
        )
    );

    if ($subscription_request->response['payload']) { 

        $cash_admin->page_data['subscriptions'] = $subscription_request->response['payload'];

    }

    error_log(print_r($cash_admin->page_data['plan'], true));

    $cash_admin->page_data['ui_title'] = $cash_admin->page_data['plan']['name'];

    $cash_admin->setPageContentTemplate('commerce_subscriptions_detail');
    ?>