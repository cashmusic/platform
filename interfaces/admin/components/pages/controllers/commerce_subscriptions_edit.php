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

$cash_admin->page_data['connection_id'] = $stripe_default;

if (!empty($_POST['action']) && $_POST['action'] == "do_update") {

            $subscription_request = new CASHRequest(
                array(
                    'cash_request_type' => 'commerce',
                    'cash_action' => 'updatesubscriptionplan',
                    'user_id' => $cash_admin->effective_user_id,
                    'connection_id' => $_POST['connection_id'],
                    'id' => $_POST['id'],
                    'sku' => $_POST['sku'],
                    'name' => (!empty($_POST['name'])) ? $_POST['name'] : false,
                    'description' => (!empty($_POST['description'])) ? $_POST['description'] : false,
                    'price' => (!empty($_POST['price'])) ? $_POST['price'] : false,
                    'flexible_price' => (!empty($_POST['flexible_price'])) ? $_POST['flexible_price'] : false,
                    'physical' => (!empty($_POST['physical'])) ? $_POST['physical'] : false
                )
            );

    if ($subscription_request->response['payload']) {



        $cash_admin->page_data['status'] = "success";
        $cash_admin->page_data['plan'] = $subscription_request->response['payload'];

        $subscription_request = new CASHRequest(
            array(
                'cash_request_type' => 'commerce',
                'cash_action' => 'getsubscriptionplan',
                'user_id' => $cash_admin->effective_user_id,
                'id' => $request_parameters[0]
            )
        );

        if ($subscription_request->response['payload']) {

            $cash_admin->page_data['plan'] = $subscription_request->response['payload'];
            $cash_admin->setPageContentTemplate('commerce_subscriptions_detail');
        } else {
            echo "error";
        }

    }
}

if (empty($_POST['action'])) {
    // plan index
    $subscription_request = new CASHRequest(
        array(
            'cash_request_type' => 'commerce',
            'cash_action' => 'getsubscriptionplan',
            'user_id' => $cash_admin->effective_user_id,
            'id' => $request_parameters[0]
        )
    );

    if ($subscription_request->response['payload']) {

        $cash_admin->page_data['plan'] = $subscription_request->response['payload'];
    }

    $cash_admin->setPageContentTemplate('commerce_subscriptions_edit');
}
    ?>