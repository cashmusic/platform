<?php
// Sale Refund Sample

// This sample code demonstrate how you can process a refund on a sale transaction created using the Payments API. API used: /v1/payments/sale/{sale-id}/refund
/** @var Sale $sale */
require  './framework/lib/paypal/autoload.php';

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Amount;
use PayPal\Api\Refund;
use PayPal\Api\Sale;

use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
// Refund amount

// Includes both the refunded amount (to Payer) and refunded fee (to Payee). Use the $amt->details field to mention fees refund details.
$amt = new Amount();
$amt->setCurrency('USD')
    ->setTotal(0.01);
// Refund object
$refund = new Refund();
$refund->setAmount($amt);
// Sale

// A sale transaction. Create a Sale object with the given sale transaction id.
$sale = new Sale();
$sale->setId("PAY-9V912506K7697313KKZS5XFI");
try {

// Create a new apiContext object so we send a new PayPal-Request-Id (idempotency) header for this resource
    $apiContext = getApiContext("AcsCTIuXz-mUA6pXEjk-CevjKaIB-Ly7o9AEReG-CuWYJfZS2bwM5rQt1XAckoaZ9_wWEnQwPh5zW8f8", "EILHNX-eER2jvPi-NVPxpjCwqbSRwku_4iNxRic0peryQd5sNuSCgW6EkfA_UkJMFvK9V01sB8qsmHF7");

// Refund the sale (See bootstrap.php for more on ApiContext)
    $refundedSale = $sale->refund($refund, $apiContext);
} catch (Exception $ex) {

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 	ResultPrinter::printError("Refund Sale", "Sale", $refundedSale->getId(), $refund, $ex);
    exit(1);
}

// NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
 ResultPrinter::printResult("Refund Sale", "Sale", $refundedSale->getId(), $refund, $refundedSale);

return $refundedSale;