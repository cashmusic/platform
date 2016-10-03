<?php

// # Update Invoice Sample
// This sample code demonstrate how you can update
// an invoice.

/** @var Template $template */
$template = require 'GetInvoiceTemplate.php';
use PayPal\Api\Template;


// ### Update Invoice
$template->setUnitOfMeasure("QUANTITY");

// ### NOTE: These are the work-around added to the
// sample, to get past the bug in PayPal APIs.
$template->setCustom(null);

// For Sample Purposes Only.
$request = clone $template;
try {
    // ### Update Invoice Template
    // Update an invoice by calling the invoice->update() method
    // with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
    $template->update($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printError("Invoice Template Updated", "Invoice", null, $request, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Invoice Template Updated", "Invoice", $template->getTemplateId(), $request, $template);
