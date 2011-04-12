<?php
/**
 *
 * PaypalSeed page-state handler script
 *
 * @package diy.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 * 
 * scans querystring values to get current page state and inititate PaypalSeed
 * objects where needed and setting a pageState variable to indicate progress
 *
 * Copyright (c) 2011, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/

// session variables (API requests/responses, state, messages, micro or regular)
session_start();
if (!isset($_SESSION['seed_state_payment'])) $_SESSION['seed_state_payment'] = 'before';
if (!isset($_SESSION['cash_primary_request'])) $_SESSION['cash_primary_request'] = false;
if (!isset($_SESSION['seed_response'])) $_SESSION['seed_response'] = false;
if (!isset($_SESSION['seed_details'])) $_SESSION['seed_details'] = false;
if (!isset($_SESSION['seed_error'])) $_SESSION['seed_error'] = false;
if (!isset($_SESSION['seed_microtransaction'])) $_SESSION['seed_microtransaction'] = false;
if (!isset($_SESSION['seed_referral'])) $_SESSION['seed_referral'] = '';

// function to set correct paypal keys
function setPaypalKeys() {
	global $paypal_address, $paypal_key, $paypal_secret;
	if ($_SESSION['seed_microtransaction']) {
		$paypal_address = PAYPAL_MICRO_ADDRESS;
		$paypal_key = PAYPAL_MICRO_KEY;
		$paypal_secret = PAYPAL_MICRO_SECRET;
	} else {
		$paypal_address = PAYPAL_ADDRESS;
		$paypal_key = PAYPAL_KEY;
		$paypal_secret = PAYPAL_SECRET;
	}
}
setPaypalKeys();
	
// grab the page URL
$url_minus_get = 'http'.((empty($_SERVER['HTTPS'])&&$_SERVER['SERVER_PORT']!=443)?'':'s').'://'.$_SERVER['HTTP_HOST'].strtok($_SERVER['REQUEST_URI'],'?');
$PaypalSeed_location = __DIR__.'/../classes/PaypalSeed.php';
$MySQLSeed_location = __DIR__.'/../classes/MySQLSeed.php';
$ProductSeed_location = __DIR__.'/../classes/ProductSeed.php';
$TransactionSeed_location = __DIR__.'/../classes/TransactionSeed.php';

if ($_REQUEST['seed_payment'] == 'go') {
	if (isset($_REQUEST['seed_referral'])) {
		$_SESSION['seed_referral'] = urldecode($_REQUEST['seed_referral']);
	}
	if (isset($_REQUEST['seed_sku'])) {
		// reset all session variables, just in case
		$_SESSION['seed_state_payment'] = 'before';
		$_SESSION['seed_response'] = false;
		$_SESSION['seed_error'] = false;
	
		include_once($PaypalSeed_location);
		include_once($MySQLSeed_location);
		include_once($ProductSeed_location);
		$db = new MySQLSeed(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
		$product = new ProductSeed($db,$_REQUEST['seed_sku']);
		$productInfo = $product->getInfo();
		
		if ($productInfo) {
			$total_amt = $productInfo['price'];
			if (isset($_REQUEST['seed_addtoamt'])) {
				$total_amt += $_REQUEST['seed_addtoamt'];
			}
			if ($total_amt < SMALLEST_ALLOWED_TRANSACTION) {
				$_SESSION['seed_state_payment'] = 'zerototal';
			} else {
				if ($total_amt < 12) {
					$_SESSION['seed_microtransaction'] = true;
					setPaypalKeys();
				}
				$pp = new PaypalSeed($paypal_address,$paypal_key,$paypal_secret);
				if (!$pp->SetExpressCheckout($total_amt,$productInfo['sku'],$productInfo['title'],$url_minus_get,$url_minus_get,false)) {
					$_SESSION['seed_state_payment'] = 'error';
					$_SESSION['seed_error'] = $pp->getErrorMessage();
				} else {
					exit;
				}
			}
		} else {
			$_SESSION['seed_error'] = 'No matching product was found.';
			$_SESSION['seed_state_payment'] = 'error';
		}
	} else {
		$_SESSION['seed_error'] = 'No product was specified.';
		$_SESSION['seed_state_payment'] = 'error';
	}
} else if (isset($_GET['token']) && isset($_GET['PayerID'])) {
	// data returned from Paypal
	include_once($PaypalSeed_location);
	$pp = new PaypalSeed($paypal_address,$paypal_key,$paypal_secret);
	$_SESSION['cash_primary_request'] = $pp->getExpressCheckout();
	$_SESSION['seed_response'] = $pp->doExpressCheckout();
	$_SESSION['seed_details'] = $pp->getExpressCheckout();
	
	// handle all processing then redirect to self, cleaning the URL
	switch ($_SESSION['seed_details']['CHECKOUTSTATUS']) {
	    case 'PaymentActionCompleted':
			if ($_SESSION['seed_response']['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Completed' || 
				$_SESSION['seed_response']['PAYMENTINFO_0_PAYMENTSTATUS'] == 'In-Progress' || 
				$_SESSION['seed_response']['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Processed' || 
				$_SESSION['seed_response']['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Pending') {
					include_once($MySQLSeed_location);
					include_once($TransactionSeed_location);
					$db = new MySQLSeed(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
					$transaction = new TransactionSeed($db);
					$_SESSION['seed_state_payment'] = 'completed';
					$success = $transaction->addTransaction(
						urldecode($_SESSION['seed_details']['TIMESTAMP']),
						urldecode($_SESSION['seed_details']['EMAIL']),
						urldecode($_SESSION['seed_details']['PAYERID']),
						urldecode($_SESSION['seed_details']['FIRSTNAME']),
						urldecode($_SESSION['seed_details']['LASTNAME']),
						urldecode($_SESSION['seed_details']['COUNTRYCODE']),
						urldecode($_SESSION['cash_primary_request']['L_PAYMENTREQUEST_0_NUMBER0']),
						urldecode($_SESSION['cash_primary_request']['L_PAYMENTREQUEST_0_NAME0']),
						urldecode($_SESSION['seed_details']['PAYMENTREQUEST_0_TRANSACTIONID']),
						$_SESSION['seed_response']['PAYMENTINFO_0_PAYMENTSTATUS'],
						urldecode($_SESSION['seed_details']['PAYMENTREQUEST_0_CURRENCYCODE']),
						urldecode($_SESSION['seed_details']['PAYMENTREQUEST_0_AMT']),
						urldecode($_SESSION['seed_response']['PAYMENTINFO_0_FEEAMT']),
						1,
						$_SESSION['seed_referral'],
						urldecode(json_encode($_SESSION['cash_primary_request'])),
						urldecode(json_encode($_SESSION['seed_response'])),
						urldecode(json_encode($_SESSION['seed_details']))
					);
					if (!$success) {
						$_SESSION['seed_error'] = mysql_error();
					}
			} else {
				$_SESSION['seed_state_payment'] = 'failed';
			}
	        break;
	    case 'PaymentActionFailed':
			$_SESSION['seed_state_payment'] = 'failed';
	        break;
	    default:
	       $_SESSION['seed_state_payment'] = 'uncompleted';
	}
	header("Location: $url_minus_get");
	exit;
} else if (isset($_GET['token']) && !isset($_GET['PayerID'])) {
	// cancellation return from Paypal
	$_SESSION['seed_state_payment'] = 'cancelled';
	header("Location: $url_minus_get");
	exit;
}
?>