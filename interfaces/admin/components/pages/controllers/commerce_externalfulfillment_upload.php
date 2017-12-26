<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHSystem as CASHSystem;
use CASHMusic\Core\CASHRequest as CASHRequest;
use ArrayIterator;
use CASHMusic\Admin\AdminHelper;
use CASHMusic\Seeds\ExternalFulfillmentSeed;

$admin_helper = new AdminHelper($admin_request, $cash_admin);

// process uploads and shit
if (!empty($_FILES)) {

$user_id = $admin_helper->getPersistentData('cash_effective_user');
$external_fulfillment = new ExternalFulfillmentSeed($user_id);

// need to setup error checking

$job_id = $external_fulfillment
        ->processUpload($_FILES['csv_upload'])
        ->createOrContinueJob();
}


?>