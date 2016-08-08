<?php

$user_id = AdminHelper::getPersistentData('cash_effective_user');
$external_fulfillment = new ExternalFulfillmentSeed($user_id);
    
$external_fulfillment
    ->createTiers();

?>