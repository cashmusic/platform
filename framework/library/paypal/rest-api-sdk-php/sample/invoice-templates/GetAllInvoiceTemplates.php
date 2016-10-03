<?php

use PayPal\Api\Templates;

require 'CreateInvoiceTemplate.php';

try {
    $templates = Templates::getAll(array("fields" => "all"), $apiContext);
}  catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printError("Get all Templates", "Templates", null, null, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
ResultPrinter::printResult("Get all Templates", "Templates", null, null, $templates);

return $templates;
