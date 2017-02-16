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

    $currency_request = new \CASHRequest(
        array(
            'cash_request_type' => 'system',
            'cash_action' => 'getsettings',
            'type' => 'use_currency',
            'user_id' => $cash_admin->effective_user_id
        )
    );

    // currency stuff
    if ($currency_request->response['payload']) {
        $cash_admin->page_data['currency'] = \CASHSystem::getCurrencySymbol($currency_request->response['payload']);
    } else {
        $cash_admin->page_data['currency'] = \CASHSystem::getCurrencySymbol('USD');
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

        foreach ($subscription_request->response['payload'] as $subscription) {
            $subscription['start_date'] = date('m/d/Y', $subscription['start_date']);
            $subscription['end_date'] = (!empty($subscription['end_date'])) ? date('m/d/Y', $subscription['end_date']) : "recurring";

            $data = json_decode($subscription['data'], true);

            $subscription['subscriber_name'] = $data['customer']['customer_name'];
            $cash_admin->page_data['subscriptions'][] = $subscription;
        }

    }

    $stats_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getsubscriptionstats',
            'plan_id' => $request_parameters[0]
        )
    );

    if ($stats_request->response['payload']) {
        $cash_admin->page_data['gross_active'] = $stats_request->response['payload'][0]['total_active'];
    }

    $stats_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getsubscribercount',
            'plan_id' => $request_parameters[0]
        )
    );

    if ($stats_request->response['payload']) {
        $cash_admin->page_data['active_subscribers'] = $stats_request->response['payload'][0]['active_subscribers'];
    }


    $cash_admin->page_data['ui_title'] = $cash_admin->page_data['plan']['name'];
    $cash_admin->page_data['plan_id'] = $cash_admin->page_data['plan']['id'];

    $cash_admin->setPageContentTemplate('commerce_subscriptions_detail');
?>