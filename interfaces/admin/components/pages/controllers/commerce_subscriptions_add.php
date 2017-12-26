<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;

$admin_helper = new AdminHelper($admin_request, $cash_admin);
$effective_user_id = $admin_helper->getPersistentData('cash_effective_user');
$settings_request = new CASHRequest(
    array(
        'cash_request_type' => 'system',
        'cash_action' => 'getsettings',
        'type' => 'payment_defaults',
        'user_id' => $effective_user_id
    )
);

$stripe_default = isset_else($settings_request->response['payload']['stripe_default'], false);

if (!empty($_POST['action']) && $_POST['action'] == "do_create") {

    // add plan
    $subscription_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'createsubscriptionplan',
            'user_id' => $effective_user_id,
            'connection_id' => $stripe_default,
            'plan_name' => $_POST['name'],
            'description' => $_POST['description'],
            'sku' => "cash_".$effective_user_id."_".uniqid(),
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

    if ($subscription_request->response['payload']) {

        $admin_helper->formSuccess('Success. Subscription plan added.','/commerce/subscriptions/detail/'.$subscription_request->response['payload']['numeric_id']);
        /*CASHSystem::redirectToUrl(CASH_ADMIN_URL."/commerce/subscriptions/detail/".$subscription_request->response['payload']['numeric_id']);*/
    } else {
        $admin_helper->formFailure('Error. Something just didn\'t work right.<br>',"/commerce/");
    }
} else {
    $cash_admin->setPageContentTemplate('commerce_subscriptions_add');
}



    ?>