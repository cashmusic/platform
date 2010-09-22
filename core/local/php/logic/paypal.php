<?php
/**
 *
 * PaypalSeed page-state handler script
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmuisc.org/
 * 
 * scans querystring values to get current page state and inititate PaypalSeed
 * objects where needed and setting a pageState variable to indicate progress
 *
 * Copyright (c) 2010, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/

// define contants (paypal credentials, db, S3, etc)
define('PAYPAL_ADDRESS', 'sell_1275529145_biz_api1.cashmusic.org');
define('PAYPAL_KEY', '1275529151');
define('PAYPAL_SECRET', 'AFcWxV21C7fd0v3bYYYRCpSSRl31AGCG62tWdLmw5MVRVpwXFOJVoCjk');

define('PAYPAL_MICRO_ADDRESS', 'sell_1275529145_biz_api1.cashmusic.org');
define('PAYPAL_MICRO_KEY', '1275529151');
define('PAYPAL_MICRO_SECRET', 'AFcWxV21C7fd0v3bYYYRCpSSRl31AGCG62tWdLmw5MVRVpwXFOJVoCjk');

define('DB_HOSTNAME', '');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_DATABASE', '');

define('SMALLEST_ALLOWED_TRANSACTION', 0.4);

define('ADD', 0.4);

// session variables (API requests/responses, state, messages, micro or regular)
session_start();
if (!isset($_SESSION['seed_state'])) $_SESSION['seed_state'] = 'before';
if (!isset($_SESSION['seed_request'])) $_SESSION['seed_request'] = false;
if (!isset($_SESSION['seed_response'])) $_SESSION['seed_response'] = false;
if (!isset($_SESSION['seed_details'])) $_SESSION['seed_details'] = false;
if (!isset($_SESSION['seed_error'])) $_SESSION['seed_error'] = false;
if (!isset($_SESSION['seed_microtransaction'])) $_SESSION['seed_microtransaction'] = false;

// function to set correct paypal keys
function setPaypalKeys();
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

if ($_REQUEST['seed_begin'] == 'go') {
	if (isset($_REQUEST['seed_sku'])) {
		// reset all session variables, just in case
		$_SESSION['seed_state'] = 'before';
		$_SESSION['seed_response'] = false;
		$_SESSION['seed_error'] = false;
	
		include($PaypalSeed_location);
		include($MySQLSeed_location);
		include($ProductSeed_location);
		$db = new MySQLSeed(DB_HOSTNAME,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
		$product = new ProductSeed($db,$_REQUEST['seed_sku']);
		$productInfo = $product->getInfo();

		if ($productInfo) {
			$total_amt = $productInfo['price'];
			if (isset($_REQUEST['seed_addtoamt'])) {
				$total_amt += $_REQUEST['seed_addtoamt'];
			}
			if ($total_amt < SMALLEST_ALLOWED_TRANSACTION) {
				$_SESSION['seed_state'] = 'zerototal';
			} else {
				if ($total_amt < 12) {
					$_SESSION['seed_microtransaction'] = true;
					setPaypalKeys();
				}
				$pp = new PaypalSeed($paypal_address,$paypal_key,$paypal_secret);
				if (!$pp->SetExpressCheckout($total_amt,$productInfo['sku'],$productInfo['title'],$url_minus_get,$url_minus_get,false)) {
					$_SESSION['seed_state'] = 'error';
					$_SESSION['seed_error'] = $pp->getErrorMessage();
				}
				exit;
			}
		} else {
			$_SESSION['seed_error'] = 'No matching product was found.';
			$_SESSION['seed_state'] = 'error';
		}
	} else {
		$_SESSION['seed_error'] = 'No product was specified.';
		$_SESSION['seed_state'] = 'error';
	}
} else if (isset($_GET['token']) && isset($_GET['PayerID'])) {
	// data returned from Paypal
	include($PaypalSeed_location);
	$pp = new PaypalSeed($paypal_address,$paypal_key,$paypal_secret);
	$_SESSION['seed_request'] = $pp->getExpressCheckout();
	$_SESSION['seed_response'] = $pp->doExpressCheckout();
	$_SESSION['seed_details'] = $pp->getExpressCheckout();
	
	// handle all processing then redirect to self, cleaning the URL
	switch ($_SESSION['seed_details']['CHECKOUTSTATUS']) {
	    case 'PaymentActionCompleted':
			if ($_SESSION['seed_response']['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Completed' || 
				$_SESSION['seed_response']['PAYMENTINFO_0_PAYMENTSTATUS'] == 'In-Progress' || 
				$_SESSION['seed_response']['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Processed' || 
				$_SESSION['seed_response']['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Pending') {
					$_SESSION['seed_state'] = 'completed';
			} else {
				$_SESSION['seed_state'] = 'failed';
			}
	        break;
	    case 'PaymentActionFailed':
			$_SESSION['seed_state'] = 'failed';
	        break;
	    default:
	       $_SESSION['seed_state'] = 'uncompleted';
	}
	header("Location: $url_minus_get");
	exit;
} else if (isset($_GET['token']) && !isset($_GET['PayerID'])) {
	// cancellation return from Paypal
	$_SESSION['seed_state'] = 'cancelled';
	header("Location: $url_minus_get");
	exit;
}
?>