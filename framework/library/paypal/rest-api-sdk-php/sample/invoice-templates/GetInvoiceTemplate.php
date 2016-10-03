<?php

// # Retrieve Invoice Template Sample
// This sample code demonstrate how you can get
// an invoice template using templateId.

use PayPal\Api\Template;

$invoiceTemplate = require 'CreateInvoiceTemplate.php';

/** @var Template $invoiceTemplate */
$templateId = $invoiceTemplate->getTemplateId();

// ### Retrieve Invoice Template
// Retrieve the invoice template object by calling the
// static `get` method
// on the Template class by passing a valid
// Template ID
// (See bootstrap.php for more on `ApiContext`)
try {
    $template = Template::get($templateId, $apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printError("Get Invoice Template", "Template", $template->getTemplateId(), $templateId, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
ResultPrinter::printResult("Get Invoice Template", "Template", $template->getTemplateId(), $templateId, $template);

return $template;
