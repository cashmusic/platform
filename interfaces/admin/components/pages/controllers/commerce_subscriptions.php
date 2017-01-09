<?php

if (isset($request_parameters[0])) {
    if ($request_parameters[0] == "delete") {
        $subscription_request = new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'deletesubscriptionplan',
                'user_id' => $cash_admin->effective_user_id,
                'id' => $request_parameters[1]
            )
        );

        error_log(
            print_r($subscription_request->response['payload'], true)
        );

        if ($subscription_request->response['payload']) {
            AdminHelper::formSuccess('Success. Subscription plan deleted. Remember to cancel the plan on Stripe because I don\'t feel comfortable doing it, Dave.','/commerce/subscriptions');
        } else {
            AdminHelper::formFailure('Error. Something just didn\'t work right.','/commerce/subscriptions');
        }

    }
}

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

// plan index
    $subscription_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getsubscriptionplans',
            'user_id' => $cash_admin->effective_user_id,
            'limit' => false
        )
    );

    if ($subscription_request->response['payload']) {

        $cash_admin->page_data['plans'] = $subscription_request->response['payload'];
    }

    $cash_admin->page_data['connection'] = AdminHelper::getConnectionsByScope('commerce');

    if (!$cash_admin->page_data['connection']) {
        $cash_admin->page_data['firstuse'] = true;
    }

    $cash_admin->setPageContentTemplate('commerce_subscriptions');
    ?>