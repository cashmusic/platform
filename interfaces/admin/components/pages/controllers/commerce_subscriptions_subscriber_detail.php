<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_primary_cash_request, $cash_admin);

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

    if (isset($request_parameters[1])) {

        switch ($request_parameters[1]) {

            case "comp":

                // make sure user is unsubscribed first
                $subscriber_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'commerce',
                        'cash_action' => 'cancelsubscription',
                        'id' => $request_parameters[0],
                        'user_id' => $cash_admin->effective_user_id,
                        'connection_id' => $stripe_default
                    )
                );

                $subscriber_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'commerce',
                        'cash_action' => 'updatesubscription',
                        'id' => $request_parameters[0],
                        'status' => "comped"
                    )
                );

                if ($subscriber_request->response['payload']) {
                    $admin_helper->formSuccess('Success. Subscriber comped for this plan.','/commerce/subscriptions/subscriber/detail/'.$request_parameters[0]);
                } else {
                    $admin_helper->formFailure('Error. Something just didn\'t work right.','/commerce/subscriptions/subscriber/detail/'.$request_parameters[0]);
                }

                break;

            case "delete":

                $subscriber_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'commerce',
                        'cash_action' => 'cancelsubscription',
                        'id' => $request_parameters[0],
                        'user_id' => $cash_admin->effective_user_id,
                        'connection_id' => $stripe_default
                    )
                );

                $subscriber_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'commerce',
                        'cash_action' => 'deletesubscription',
                        'id' => $request_parameters[0],
                        'subscription_id' => $request_parameters[2]
                    )
                );



                if ($subscriber_request->response['payload']) {
                    $admin_helper->formSuccess('Success. Subscriber deleted.','/commerce/subscriptions/detail/'.$request_parameters[2]);
                } else {
                    $admin_helper->formFailure('Error. Something just didn\'t work right.','/commerce/subscriptions/subscriber/detail/'.$request_parameters[0]);
                }

                break;

            case "cancel":
                $subscriber_request = new CASHRequest(
                    array(
                        'cash_request_type' => 'commerce',
                        'cash_action' => 'cancelsubscription',
                        'id' => $request_parameters[0],
                        'user_id' => $cash_admin->effective_user_id,
                        'connection_id' => $stripe_default
                    )
                );
                if ($subscriber_request->response['payload']) {
                    $admin_helper->formSuccess('Success. Subscriber unsubscribed.','/commerce/subscriptions/detail/'.$request_parameters[2]);
                } else {
                    $admin_helper->formFailure('Error. Something just didn\'t work right.','/commerce/subscriptions/subscriber/detail/'.$request_parameters[0]);
                }

                break;

            default:
                //
        }
    }

    $subscriber_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getsubscriptiondetails',
            'id' => $request_parameters[0]
        )
    );
    CASHSystem::errorLog($subscriber_request);
    if ($subscriber_request->response['payload']) {

        // get subscription details
        $subscription_details = $subscriber_request->response['payload']->toArray();

        $data = $subscription_details['data'];

        $cash_admin->page_data['subscriber'] = $subscription_details;

        $cash_admin->page_data['subscription_id'] = $subscription_details['subscription_id'];

        $cash_admin->page_data['subscriber']['creation_date'] = date("F jS, Y", $cash_admin->page_data['subscriber']['creation_date']);
        $cash_admin->page_data['customer'] = $data['customer'];
        $cash_admin->page_data['shipping_info'] = $data['shipping_info'];

        // get transactions for subscription

        $subscriber_request = new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'getsubscriptiontransactions',
                'id' => $request_parameters[0]
            )
        );

        $plan_request = new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'getsubscriptionplan',
                'user_id' => $cash_admin->effective_user_id,
                'id' => $subscription_details['subscription_id']
            )
        );

        if ($plan_request->response['payload']) {
            $cash_admin->page_data['plan'] = $plan_request->response['payload']->toArray();
        }


        if ($subscriber_request->response['payload']) {
            $payments = $subscriber_request->response['payload'];

            foreach ($payments as $payment) {
                $payment = $payment->toArray();
                $payment['service_timestamp'] = date('m/d/Y g:i A', $payment['service_timestamp']);
                $cash_admin->page_data['subscriptions_payment'][] = $payment;
            }
        }

    }

/*    $subscription_request = new CASHRequest(
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
            $cash_admin->page_data['subscriptions'][] = $subscription;
        }

    }*/


    $cash_admin->page_data['id'] = $cash_admin->page_data['subscriber']['id'];

    $cash_admin->setPageContentTemplate('commerce_subscriptions_subscriber_detail');
    ?>