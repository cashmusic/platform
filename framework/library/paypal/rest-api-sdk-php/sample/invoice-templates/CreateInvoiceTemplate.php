<?php

// # Create Invoice Template Sample
// This sample code demonstrate how you can create
// an invoice template.

use PayPal\Api\Currency;
use PayPal\Api\InvoiceItem;
use PayPal\Api\MerchantInfo;
use PayPal\Api\Template;
use PayPal\Api\TemplateData;
use PayPal\Api\TemplateSettings;
use PayPal\Api\TemplateSettingsMetadata;

require __DIR__ . '/../bootstrap.php';

// ### Invoice Template Item
$invoiceTemplateDataItem = new InvoiceItem();
$invoiceTemplateDataItem
    ->setName("Nutri Bullet")
    ->setQuantity(1)
    ->setUnitPrice(new Currency('{ "currency": "USD", "value": "50.00" }'));

// ### Invoice Template Data
$invoiceTemplateData = new TemplateData();
$invoiceTemplateData
    ->setTaxCalculatedAfterDiscount(false)
    ->setTaxInclusive(false)
    ->setNote("Thank you for your business")
    ->setLogoUrl("https://pics.paypal.com/v1/images/redDot.jpeg")
    ->addItem($invoiceTemplateDataItem)
    ->setMerchantInfo(new MerchantInfo('{ "email": "jaypatel512-facilitator@hotmail.com" }'));

// ### Template Settings
$displayPreferences = new TemplateSettingsMetadata();
$displayPreferences->setHidden(true);

$settingDate = new TemplateSettings();
$settingDate
    ->setFieldName("items.date")
    ->setDisplayPreference($displayPreferences);


// ### Template
$invoiceTemplate = new Template();
$invoiceTemplate
    ->setName("Hours Template" . rand())
    ->setDefault(true)
    ->setUnitOfMeasure("HOURS")
    ->setTemplateData($invoiceTemplateData)
    // This is another way of initializing the object.
    ->addSetting(new TemplateSettings('{ "field_name": "custom", "display_preference": { "hidden": true } }'))
    ->addSetting($settingDate);

// For Sample Purposes Only.
$request = clone $invoiceTemplate;

try {
    // ### Create Invoice Template
    // Create an invoice by calling the invoice->create() method
    // with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
    $invoiceTemplate->create($apiContext);
} catch (Exception $ex) {
    // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
    ResultPrinter::printError("Create Invoice Template", "Template", null, $request, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
ResultPrinter::printResult("Create Invoice Template", "Template", $invoiceTemplate->getTemplateId(), $request, $invoiceTemplate);

return $invoiceTemplate;
