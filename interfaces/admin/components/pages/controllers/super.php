<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;


$admin_helper = new AdminHelper($admin_primary_cash_request, $cash_admin);

$effective_user_id = $admin_helper->getPersistentData('cash_effective_user');

if (!in_array($effective_user_id, [103457, 1])) {
    echo "Sorry";
    exit;
}

$get_user = new CASHRequest(
    array(
        'cash_request_type' => 'admin',
        'cash_action' => 'getschema',
        'table' => $effective_user_id,
        'limit' => false
    )
);

$cash_admin->page_data['data'] =  json_encode($get_user->response, JSON_PRETTY_PRINT);

$cash_admin->setPageContentTemplate('super');