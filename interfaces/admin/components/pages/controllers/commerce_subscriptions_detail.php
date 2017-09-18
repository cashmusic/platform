<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;
use Maatwebsite\Excel\Facades\Excel;

$admin_helper = new AdminHelper($admin_primary_cash_request, $cash_admin);

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
            'email_address' => $_POST['email_address']
        )
    );

    if ($comped_request->response['payload']) {
        $admin_helper->formSuccess('Success. Comped subscription added to this plan.','/commerce/subscriptions/detail/'.$request_parameters[0]);
    } else {
        $admin_helper->formFailure('Error. Something just didn\'t work right.',"/commerce/subscriptions/detail/".$request_parameters[0]);
    }
}

if (isset($_REQUEST['export'])) {

    $subscription_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getallsubscriptionsbyplan',
            'id' => $request_parameters[0]
        )
    );

    if ($data = $subscription_request->response['payload']) {

        $filename = "cash-subscription-".$request_parameters[0].date('mdY', time());
        Excel::create($filename, function($excel) use ($data, $filename) {

            $excel->sheet($filename, function($sheet) use ($data) {
                /*$sheet->setAutoSize([
                    'A', 'F', 'H', 'I'
                ]);

                $sheet->setWidth(array(
                    'B'     =>  60,
                    'C'     =>  60,
                    'G'     => 50,
                    'I'     => 10,
                    'J'     => 40

                ));*/

                $sheet->fromArray($data);
            });

        })->download('csv');
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

    $plan_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getsubscriptionplan',
            'user_id' => $cash_admin->effective_user_id,
            'id' => $request_parameters[0]
        )
    );

    if ($plan_request->response['payload']) {

        $cash_admin->page_data['plan'] = $plan_request->response['payload']->toArray();
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