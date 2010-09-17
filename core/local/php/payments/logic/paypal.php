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

// define paypal credentials
define('PAYPAL_ADDRESS', 'sell_1275529145_biz_api1.cashmusic.org');
define('PAYPAL_KEY', '1275529151');
define('PAYPAL_SECRET', 'AFcWxV21C7fd0v3bYYYRCpSSRl31AGCG62tWdLmw5MVRVpwXFOJVoCjk');

// define session variables
session_start();
if (!isset($_SESSION['seed_state'])) $_SESSION['seed_state'] = 'before';
if (!isset($_SESSION['seed_response'])) $_SESSION['seed_response'] = false;
if (!isset($_SESSION['seed_error'])) $_SESSION['seed_error'] = false;

if ($_GET['paypalseed'] == 'go') {
	// reset all session variables, just in case
	$_SESSION['seed_state'] = 'before';
	$_SESSION['seed_response'] = false;
	$_SESSION['seed_error'] = false;
	
	include(__DIR__.'/../classes/paypal.php');
	$seed_ppobj = new PaypalSeed(PAYPAL_ADDRESS,PAYPAL_KEY,PAYPAL_SECRET);
	if (!$seed_ppobj->SetExpressCheckout(5,'stuff','the finest stuff in the world','http://cashmusic.org/tools/_payments/','http://cashmusic.org/tools/_payments/',false,true,false,false,'USD','Sale',false,'000000','000000','000000')) {
		$_SESSION['seed_state'] = 'error';
		$_SESSION['seed_error'] = $seed_ppobj->getErrorMessage();
	}
} else if (isset($_GET['token']) && isset($_GET['PayerID'])) {
	// data returned from Paypal
	$_SESSION['seed_state'] = 'after';
	include(__DIR__.'/../classes/paypal.php');
	$seed_ppobj = new PaypalSeed(PAYPAL_ADDRESS,PAYPAL_KEY,PAYPAL_SECRET);
	$seed_ppobj->doExpressCheckout();
	$_SESSION['seed_response'] = $seed_ppobj->getExpressCheckout();
	// handle all processing then redirect to self, cleaning the URL
	$urlMinusGet = 'http'.((empty($_SERVER['HTTPS'])&&$_SERVER['SERVER_PORT']!=443)?'':'s').'://'.$_SERVER['HTTP_HOST'].strtok($_SERVER['REQUEST_URI'],'?');
	header("Location: $urlMinusGet");
} else if (isset($_GET['token']) && !isset($_GET['PayerID'])) {
	// cancellation return from Paypal
	$_SESSION['seed_state'] = 'cancelled';
}
?>