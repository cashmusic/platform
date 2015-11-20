<?php
/**
 * The PaypalSeed class speaks to the Paypal REST API.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * began with official Paypal SDK examples, much editing later...
 * original script(s) here:
 * https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/library_download_sdks#NVP
 *
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 *
 * This file is generously sponsored by Justin Miranda
 *
 **/
//namespace Seeds\PaypalSeed;

require CASH_PLATFORM_ROOT  . '/lib/paypal/autoload.php';

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

class PaypalSeed extends SeedBase {
	protected $api_username, $api_password, $api_signature, $api_endpoint, $api_version, $paypal_base_url, $error_message, $token;
	protected $merchant_email = false;

	public function __construct($user_id, $connection_id, $token=false) {
		$this->settings_type = 'com.paypal';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;

		if ($this->getCASHConnection()) {

			$this->account  = $this->settings->getSetting('account');
			$this->client_id  = $this->settings->getSetting('client_id');
			$this->secret = $this->settings->getSetting('secret');
			$sandboxed           = $this->settings->getSetting('sandboxed');

			$this->api_context = new \PayPal\Rest\ApiContext(
					new \PayPal\Auth\OAuthTokenCredential(
						$this->client_id,		# ClientID
						$this->secret			# ClientSecret
					)
				);

			if (!$this->account || !$this->client_id || !$this->secret) {
				$connections = CASHSystem::getSystemSettings('system_connections');

				if (isset($connections['com.paypal'])) {
					$this->merchant_email = $this->settings->getSetting('merchant_email'); // present in multi
					$this->account   = $connections['com.paypal']['account'];
					$this->client_id   = $connections['com.paypal']['client_id'];
					$this->secret  = $connections['com.paypal']['secret'];
					$sandboxed            = $connections['com.paypal']['sandboxed'];

					$this->api_context = new \PayPal\Rest\ApiContext(
						new \PayPal\Auth\OAuthTokenCredential(
							$this->client_id,		# ClientID
							$this->secret			# ClientSecret
						)
					);
				}
			}


		} else {
			$this->error_message = 'could not get connection settings';
		}
	}

	public static function getRedirectMarkup($data=false) {
		$connections = CASHSystem::getSystemSettings('system_connections');

		// I don't like using ADMIN_WWW_BASE_PATH below, but as this call is always called inside the
		// admin I'm just going to do it. Without the full path in the form this gets all fucky
		// and that's no bueno.

		if (isset($connections['com.paypal'])) {
			$return_markup = '<h4>Paypal</h4>'
						   . '<p>You\'ll need a verified Business or Premier Paypal account to connect properly. '
						   . 'Those are free upgrades, so just double-check your address and enter it below. You '
						   . 'can learn more about what they entail <a href="https://cms.paypal.com/cgi-bin/?cmd=_render-content&content_ID=developer/EC_setup_permissions">here</a>.</p>'
						   . '<form accept-charset="UTF-8" method="post" id="paypal_connection_form" action="' . ADMIN_WWW_BASE_PATH . '/settings/connections/add/com.paypal">'
						   . '<input type="hidden" name="dosettingsadd" value="makeitso" />'
						   . '<input type="hidden" name="permission_type" value="accelerated" />'
						   . '<input id="connection_name_input" type="hidden" name="settings_name" value="(Paypal)" />'
						   . '<input type="hidden" name="settings_type" value="com.paypal" />'
						   . '<label for="merchant_email">Your Paypal email address:</label>'
						   . '<input type="text" name="merchant_email" id="merchant_email" value="" />'
						   . '<br />'
						   . '<div><input class="button" type="submit" value="Add The Connection" /></div>'
						   . '</form>'
						   . '<script type="text/javascript">'
						   . '$("#paypal_connection_form").submit(function() {'
						   . '	var newvalue = $("#merchant_email").val() + " (Paypal)";'
						   . '	$("#connection_name_input").val(newvalue);'
						   . '});'
						   . '</script>';
			return $return_markup;
		} else {
			return 'Please add default paypal api credentials.';
		}
	}

	protected function setErrorMessage($msg) {
		$this->error_message = $msg;
	}

	public function getErrorMessage() {
		return $this->error_message;
	}

	protected function postToPaypal($method_name, $nvp_parameters) {
		// Set the API operation, version, and API signature in the request.
		$request_parameters = array (
			'METHOD'    => $method_name,
			'VERSION'   => $this->api_version,
			'PWD'       => $this->api_password,
			'USER'      => $this->api_username,
			'SIGNATURE' => $this->api_signature
		);
		if ($this->merchant_email) {
			$request_parameters['SUBJECT'] = $this->merchant_email;
		}
		$request_parameters = array_merge($request_parameters,$nvp_parameters);

		// Get response from the server.
		$http_response = CASHSystem::getURLContents($this->api_endpoint,$request_parameters,true);
		if ($http_response) {
			// Extract the response details.
			$http_response = explode("&", $http_response);
			$parsed_response = array();
			foreach ($http_response as $i => $value) {
				$tmpAr = explode("=", $value);
				if(sizeof($tmpAr) > 1) {
					$parsed_response[$tmpAr[0]] = urldecode($tmpAr[1]);
				}
			}

			if((0 == sizeof($parsed_response)) || !array_key_exists('ACK', $parsed_response)) {
				$this->setErrorMessage("Invalid HTTP Response for POST (" . $nvpreq . ") to " . $this->api_endpoint);
				return false;
			}

			if("SUCCESS" == strtoupper($parsed_response["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($parsed_response["ACK"])) {
				return $parsed_response;
			} else {
				$this->setErrorMessage(print_r($parsed_response, true));
				return false;
			}
		} else {
			$this->setErrorMessage('could not reach Paypal servers');
			return false;
		}
	}

	public function setCheckout(
		$payment_amount,
		$ordersku,
		$ordername,
		$return_url,
		$cancel_url,
		$request_shipping_info=true,
		$allow_note=false,
		$currency_id='USD', /* 'USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD' */
		$payment_type='sale', /* 'Sale', 'Order', or 'Authorization' */
		$invoice=false,
		$shipping_amount=false
	) {


		$payer = new Payer();
		$payer->setPaymentMethod("paypal");

		if ($request_shipping_info && $shipping_amount > 0) {
			$shipping = new Details();
			$shipping->setShipping($shipping_amount)
				//->setTax(1.3)
				->setSubtotal($payment_amount-$shipping_amount); //TODO: assumes shipping cost is passed in as part of the total $payment_amount, at the moment
		} else {
			$shipping = "";
		}

		$amount = new Amount();
		$amount->setCurrency($currency_id)
			->setTotal($payment_amount);

		if ($request_shipping_info) {
			$amount->setDetails($shipping);
		}

		$transaction = new Transaction();
		$transaction->setAmount($amount)
			->setDescription($ordername)
			->setInvoiceNumber($ordersku);

		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl($return_url)
					 ->setCancelUrl($cancel_url);


		$payment = new Payment();
		$payment->setIntent($payment_type)
			->setPayer($payer)
			->setRedirectUrls($redirectUrls)
			->setTransactions(array($transaction));
		error_log( print_r($payment, true) );
		exit;

		try { $payment->create($this->api_context); } catch (Exception $ex) {

			$error = json_decode($ex->getData());
			$this->setErrorMessage($error->message);
		}

		$approval_url = $payment->getApprovalLink();

		if (!empty($approval_url)) {
			return $approval_url;
		} else {
			// approval link isn't set, return to page and post error
			$this->setErrorMessage('There was an error contacting PayPal for this payment.');
		}
	}

	public function getExpressCheckout() {
		if ($this->token) {
			$nvp_parameters = array(
				'TOKEN' => $this->token
			);
			$parsed_response = $this->postToPaypal('GetExpressCheckoutDetails', $nvp_parameters);
			if (!$parsed_response) {
				$this->setErrorMessage('GetExpressCheckoutDetails failed: ' . $this->getErrorMessage());
				return false;
			} else {
				return $parsed_response;
			}
		} else {
			$this->setErrorMessage("No token was found.");
			return false;
		}
	}

	public function doExpressCheckout($payment_type='Sale') {
		if ($this->token) {
			$token_details = $this->getExpressCheckout();
			$nvp_parameters = array(
				'TOKEN' => $this->token,
				'PAYERID' => $token_details['PAYERID'],
				'PAYMENTREQUEST_0_PAYMENTACTION' => $payment_type,
				'PAYMENTREQUEST_0_AMT' => $token_details['PAYMENTREQUEST_0_AMT'],
				'PAYMENTREQUEST_0_CURRENCYCODE' => $token_details['PAYMENTREQUEST_0_CURRENCYCODE'],
				'PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD' => 'InstantPaymentOnly'
			);

			$parsed_response = $this->postToPaypal('DoExpressCheckoutPayment', $nvp_parameters);
			if (!$parsed_response) {
				$this->setErrorMessage($this->getErrorMessage());
				return false;
			} else {
				return $parsed_response;
			}
		} else {
			$this->setErrorMessage("No token was found.");
			return false;
		}
	}

	public function doRefund($transaction_id,$note=false,$refund_amount=0,$fullrefund=true,$currency_id='USD') {
		if ($fullrefund) {
			$refund_type = "Full";
		} else {
			$refund_type = "Partial";
		}

		$nvp_parameters = array (
			'TRANSACTIONID' => $transaction_id,
			'REFUNDTYPE' => $refund_type,
			'CURRENCYCODE' => $currency_id
		);

		if($note) {
			$nvp_parameters['NOTE'] = $note;
		}

		if (!$fullrefund) {
			if(!isset($refund_amount)) {
				$this->setErrorMessage('Partial Refund: must specify amount.');
				return false;
			} else {
				$nvp_parameters['AMT'] = $refund_amount;
			}

			if(!$note) {
				$this->setErrorMessage('Partial Refund: must specify memo.');
				return false;
			}
		}

		$parsed_response = $this->postToPaypal('RefundTransaction', $nvp_parameters);

		if (!$parsed_response) {
			$this->setErrorMessage('RefundTransaction failed: ' . $this->getErrorMessage());
			error_log($this->getErrorMessage());
			return false;
		} else {
			return $parsed_response;
		}
	}
} // END class
?>
