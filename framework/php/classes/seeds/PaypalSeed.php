<?php
/**
 * The PaypalSeed class speaks to the Paypal NVP API.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * began with official Paypal SDK examples, much editing later...
 * original script(s) here:
 * https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/library_download_sdks#NVP
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class PaypalSeed extends SeedBase {
	protected $api_username, $api_password, $api_signature, $api_endpoint, $api_version, $paypal_base_url, $error_message, $token;

	public function __construct($user_id, $connection_id, $token=false) {
		$this->settings_type = 'com.mailchimp';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		if ($this->getCASHConnection()) {
			$this->api_version = '63.0';
			$this->api_username  = $this->settings->getSetting('username');
			$this->api_password  = $this->settings->getSetting('password');
			$this->api_signature = $this->settings->getSetting('signature');
			
			$this->token = $token;
			
			$this->api_endpoint = "https://api-3t.paypal.com/nvp";
			$this->paypal_base_url = "https://www.paypal.com/webscr&cmd=";
			if ($this->settings->getSetting('sandboxed')) {
				$this->api_endpoint = "https://api-3t.sandbox.paypal.com/nvp";
				$this->paypal_base_url = "https://www.sandbox.paypal.com/webscr&cmd=";
			}
		} else {
			$this->error_message = 'could not get connection settings';
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
	
	public function setExpressCheckout(
		$payment_amount,
		$ordersku,
		$ordername,
		$return_url,
		$cancel_url,
		$request_shipping_info=true,
		$allow_note=false,
		$currency_id='USD', /* 'USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD' */
		$payment_type='Sale', /* 'Sale', 'Order', or 'Authorization' */
		$invoice=false
	) {
		// Set NVP variables:
		$nvp_parameters = array(
			'PAYMENTREQUEST_0_AMT' => $payment_amount,
			'PAYMENTREQUEST_0_PAYMENTACTION' => $payment_type,
			'PAYMENTREQUEST_0_CURRENCYCODE' => $currency_id,
			'PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD' => 'InstantPaymentOnly',
			'PAYMENTREQUEST_0_DESC' => $ordername,
			'RETURNURL' => $return_url,
			'CANCELURL' => $cancel_url,
			'L_PAYMENTREQUEST_0_AMT0' => $payment_amount,
			'L_PAYMENTREQUEST_0_NUMBER0' => $ordersku,
			'L_PAYMENTREQUEST_0_NAME0' => $ordername,
			'NOSHIPPING' => '0',
			'ALLOWNOTE' => '0',
			'SOLUTIONTYPE' => 'Sole',
			'LANDINGPAGE' => 'Billing'
		);
		if ($request_shipping_info) {
			$nvp_parameters['NOSHIPPING'] = 1;
		}
		if ($allow_note) {
			$nvp_parameters['ALLOWNOTE'] = 1;
		}
		if ($invoice) {
			$nvp_parameters['PAYMENTREQUEST_0_INVNUM'] = $invoice;
		}
		
		$parsed_response = $this->postToPaypal('SetExpressCheckout', $nvp_parameters);
		if (!$parsed_response) {
			$this->setErrorMessage('SetExpressCheckout failed: ' . $this->getErrorMessage());
			return false;
		} else {
			// Redirect to paypal.com.
			$token = urldecode($parsed_response["TOKEN"]);
			$paypal_url = $this->paypal_base_url . "_express-checkout&token=$token";
			return $paypal_url;
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
	
	public function doRefund($transaction_id, $refund_amount, $fullrefund=true,$memo=false,$currency_id='USD') {
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

		if($memo) {
			$nvp_parameters['NOTE'] = $memo;
		}
		
		if (!$fullrefund) {
			if(!isset($refund_amount)) {
				$this->setErrorMessage('Partial Refund: must specify amount.');
				return false;
			} else {
				$nvp_parameters['AMT'] = $refund_amount;
			}

			if(!$memo) {
				$this->setErrorMessage('Partial Refund: must specify memo.');
				return false;
			}
		}
		
		$parsed_response = $this->postToPaypal('RefundTransaction', $nvp_parameters);

		if (!$parsed_response) {
			$this->setErrorMessage('RefundTransaction failed: ' . $this->getErrorMessage());
			return false;
		}
	}
} // END class 
?>