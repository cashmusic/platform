<?php

// # Delete Invoice Template Sample
// This sample code demonstrate how you can delete
// an invoice template

/** @var Template $template */
$template = require 'CreateInvoiceTemplate.php';

use PayPal\Api\Template;

try {

    // ### Delete Invoice Template
    // Delete invoice object by calling the
    // `delete` method
    // on the Invoice Template class by passing a valid
    // notification object
    // (See bootstrap.php for more on `ApiContext`)
    $deleteStatus = $template->delete($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printError("Delete Invoice Template", "Template", null, $deleteStatus, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Delete Invoice Template", "Template", $template->getTemplateId(), null, null);
