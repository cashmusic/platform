<?php

// process uploads and shit
if (!empty($_FILES)) {
    if (CASH_DEBUG) {
/*        error_log(
            print_r($_FILES, true)
        );*/
    }

$user_id = AdminHelper::getPersistentData('cash_effective_user');
$external_fulfillment = new ExternalFulfillmentSeed($user_id);
    
$external_fulfillment
    ->processUpload($_FILES['csv_upload'])
    ->createJob();

}


?>