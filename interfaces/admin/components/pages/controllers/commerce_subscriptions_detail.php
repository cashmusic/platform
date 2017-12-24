<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;

$admin_helper = new AdminHelper($admin_primary_cash_request, $cash_admin);

// get plan, including element ID
$plan_request = new CASHRequest(
    array(
        'cash_request_type' => 'commerce',
        'cash_action' => 'getsubscriptionplan',
        'user_id' => $cash_admin->effective_user_id,
        'id' => $request_parameters[0]
    )
);

//CASHSystem::errorLog($plan_request->response);
if ($plan_request->response['payload']) {

    $cash_admin->page_data['plan'] = $plan_request->response['payload'];
}

// export
if (isset($_REQUEST['export'])) {

    $subscription_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getallsubscriptionsbyplan',
            'id' => $request_parameters[0]
        )
    );

    if ($subscribers = $subscription_request->response['payload']) {

        $filename = "cash-subscription-export-".$request_parameters[0].date('mdY', time()).".csv";
        $data = [];
        foreach($subscribers as $subscriber) {
            $subscription_date = date('m-d-Y', $subscriber['start_date']);
            $formatted_data = [];
            if (isset($subscriber['data']['customer'])) $formatted_data = $subscriber['data']['customer'];

            if (isset($subscriber['data']['shipping_info']) && is_array($subscriber['data']['shipping_info'])) {
                $formatted_data = array_merge($subscriber['data']['shipping_info'], $formatted_data);
            }


            $data[] = array_merge(
                $formatted_data,
                [
                    'status'=>$subscriber['status'],
                    'total_paid_to_date'=>$subscriber['total_paid_to_date'],
                    'subscriber_since'=>$subscription_date
                ]);
        }
/*
 * [customer_shipping_name] => Thomas Filepp
            [customer_address1] => 123 22j1jsjsjs
            [customer_address2] =>
            [customer_city] => Portland
            [customer_region] => OR
            [customer_postalcode] => 97202
            [customer_countrycode] =>
            [customer_email] => tom+fooo@paperscissorsandglue.com
            [customer_name] => Thomas FIFLIFP
            [customer_first_name] => Thomas
            [customer_last_name] => FIFLIFP
            [status] => active
            [total_paid_to_date] => 12
            [subscriber_since] => 08-25-2017
 */
        CASHSystem::outputArrayToCSV($data, [
            'Shipping Name',
            'Shipping Address 1',
            'Shipping Address 2',
            'Shipping City',
            'Shipping Region',
            'Shipping Postal',
            'Shipping Country',
            'Email',
            'Full Name',
            'First Name',
            'Last Name',
            'Subscription Status',
            'Total Paid To Date',
            'Subscriber Since'
        ], $filename);
    }
}

// comped subscription
if (!empty($_POST['action']) && $_POST['action'] == "create_subscription") {

    $comped_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'createcompedsubscription',
            'user_id' => $cash_admin->effective_user_id,
            'plan_id' => $request_parameters[0],
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email_address' => $_POST['email_address'],
            'element_id' =>  isset($cash_admin->page_data['plan']['element_id']) ? $cash_admin->page_data['plan']['element_id'] : false
        )
    );

    if ($comped_request->response['payload']) {
        $admin_helper->formSuccess('Success. Comped subscription added to this plan.','/commerce/subscriptions/detail/'.$request_parameters[0]);
    } else {
        $admin_helper->formFailure('Error. Something just didn\'t work right.',"/commerce/subscriptions/detail/".$request_parameters[0]);
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

    $currency_request = new CASHRequest(
        array(
            'cash_request_type' => 'system',
            'cash_action' => 'getsettings',
            'type' => 'use_currency',
            'user_id' => $cash_admin->effective_user_id
        )
    );

    // currency stuff
    if ($currency_request->response['payload']) {
        $cash_admin->page_data['currency'] = CASHSystem::getCurrencySymbol($currency_request->response['payload']);
    } else {
        $cash_admin->page_data['currency'] = CASHSystem::getCurrencySymbol('USD');
    }

// searching for a subscriber
$cash_admin->page_data['display_search'] = "";
if (!empty($_REQUEST['search'])) {
    $cash_admin->page_data['search'] = trim($_REQUEST['search']);

    if (!in_array($_REQUEST['search'], ['active', 'canceled', 'created', 'failed', 'comped'])) {
        $cash_admin->page_data['display_search'] = trim($_REQUEST['search']);
    }

    $subscription_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'searchsubscriptionsbyplan',
            'id' => $request_parameters[0],
            'search' => $cash_admin->page_data['search']
        )
    );

} else {
    $subscription_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getallsubscriptionsbyplan',
            'id' => $request_parameters[0]
        )
    );
}


    if ($subscription_request->response['payload']) {

        foreach ($subscription_request->response['payload'] as $subscription) {
            //$subscription = $subscription->toArray();
            $subscription['start_date'] = date('m/d/Y', $subscription['start_date']);
            $subscription['end_date'] = (!empty($subscription['end_date'])) ? date('m/d/Y', $subscription['end_date']) : "recurring";

            $data = $subscription['data'];

            if (isset($data['customer'])) {
                $subscription['subscriber_name'] = $data['customer']['customer_name'];
                if (!isset($subscription['email_address'])) $subscription['email_address'] = $data['customer']['customer_email'];
            }



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

    $cash_admin->page_data['gross_active'] = 0.00;
    if ($stats_request->response['payload']) {
        $cash_admin->page_data['gross_active'] = $stats_request->response['payload'];
    }

    $stats_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getsubscribercount',
            'plan_id' => $request_parameters[0]
        )
    );

    $cash_admin->page_data['active_subscribers'] = 0;
    if ($stats_request->response['payload']) {
        $cash_admin->page_data['active_subscribers'] = $stats_request->response['payload'];
    }


    $cash_admin->page_data['ui_title'] = $cash_admin->page_data['plan']['name'];
    $cash_admin->page_data['plan_id'] = $cash_admin->page_data['plan']['id'];

    $cash_admin->setPageContentTemplate('commerce_subscriptions_detail');
?>