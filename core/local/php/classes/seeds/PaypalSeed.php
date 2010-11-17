<?php
/**
 * The PaypalSeed class sets up cURL and speaks to the Paypal NVP API.
 *
 * @package seed.org.cashmusic
 * @author Jesse von Doom / CASH Music
 * @link http://cashmusic.org/
 *
 * began with official Paypal examples, much editing and pushing...
 *
 * Copyright (c) 2010, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class PaypalSeed {
	protected $api_username, $api_password, $api_signature, $api_endpoint, $api_version, $paypal_base_url, $error_message, $token;

	public function __construct($username, $password, $signature, $environment='sandbox', $token_=false) {
		// Set up your API credentials, PayPal end point, and API version.
		$this->api_username = urlencode($username);
		$this->api_password = urlencode($password);
		$this->api_signature = urlencode($signature);
		$this->api_version = urlencode('63.0');
		$this->error_message = 'No error.';
		
		// Check for GET token, else set false or manually-set at start
		if (isset($_GET['token'])) {
			$this->token = urldecode($_GET['token']);
		} else {
			$this->token = $token_;
		}
		
		// Environments: 'sandbox' / 'beta-sandbox' / 'live'
		$this->api_endpoint = "https://api-3t.paypal.com/nvp";
		$this->paypal_base_url = "https://www.paypal.com/webscr&cmd=";
		if("sandbox" === $environment || "beta-sandbox" === $environment) {
			$this->api_endpoint = "https://api-3t.$environment.paypal.com/nvp";
			$this->paypal_base_url = "https://www.$environment.paypal.com/webscr&cmd=";
		}
	}

	protected function prepCURL() {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->api_endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		return $ch;
	}
	
	protected function postToPaypal($methodName_, $nvp_str_) {
		// Set the API operation, version, and API signature in the request.
		$nvpreq = "METHOD=$methodName_&VERSION=" . $this->api_version;
		$nvpreq .= "&PWD=" . $this->api_password;
		$nvpreq .= "&USER=" . $this->api_username;
		$nvpreq .= "&SIGNATURE=" . $this->api_signature;
		$nvpreq .= $nvp_str_;

		// Set the request as a POST FIELD for curl.
		$curlobj = $this->prepCURL();
		curl_setopt($curlobj, CURLOPT_POSTFIELDS, $nvpreq);

		// Get response from the server.
		$httpResponse = curl_exec($curlobj);

		if(!$httpResponse) {
			exit('$methodName_ failed: '.curl_error($curlobj).'('.curl_errno($curlobj).')');
		}

		// Extract the response details.
		$http_response = explode("&", $httpResponse);
		$parsed_response = array();
		foreach ($http_response as $i => $value) {
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) {
				$parsed_response[$tmpAr[0]] = $tmpAr[1];
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
	}
	
	protected function setErrorMessage($msg) {
		$this->error_message = $msg;
	}
	
	public function getErrorMessage() {
		return $this->error_message;
	}
	
	public function setExpressCheckout(
		$payment_amount,
		$ordersku,
		$ordername,
		$return_url,
		$cancel_url,
		$request_shipping_info=true,
		$allow_cc=true,
		$allow_note=false,
		$invoice=false,
		$currency_id='USD', /* 'USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD' */
		$payment_type='Sale', /* 'Sale', 'Order', or 'Authorization' */
		$hdr_img=false, /* use https:// location to avoid errors */
		$hdr_img_bordercolor='FFFFFF',
		$hdr_bgcolor='FFFFFF',
		$page_bgcolor='FFFFFF'
	) {
		// Set NVP variables:
		$nvp_str = "&PAYMENTREQUEST_0_AMT=" . urlencode($payment_amount);
		$nvp_str .= "&PAYMENTREQUEST_0_PAYMENTACTION=" . urlencode($payment_type);
		$nvp_str .= "&PAYMENTREQUEST_0_CURRENCYCODE=" . urlencode($currency_id);
		$nvp_str .= "&PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD=InstantPaymentOnly";
		$nvp_str .= "&PAYMENTREQUEST_0_DESC=" . urlencode($ordername);
		$nvp_str .= "&RETURNURL=" . urlencode($return_url);
		$nvp_str .= "&CANCELURL=" . urlencode($cancel_url);
		$nvp_str .= "&L_PAYMENTREQUEST_0_AMT0=" . urlencode($payment_amount);
		$nvp_str .= "&L_PAYMENTREQUEST_0_NUMBER0=" . urlencode($ordersku);
		$nvp_str .= "&L_PAYMENTREQUEST_0_NAME0=" . urlencode($ordername);
		if ($request_shipping_info) {
			$nvp_str .= "&NOSHIPPING=0";
		} else {
			$nvp_str .= "&NOSHIPPING=1";
		}
		if ($allow_note) {
			$nvp_str .= "&ALLOWNOTE=1";
		} else {
			$nvp_str .= "&ALLOWNOTE=0";
		}
		if ($invoice) {
			$nvp_str .= "&PAYMENTREQUEST_0_INVNUM=" . urlencode($invoice);
		}
		if ($allow_cc) {
			$nvp_str .= "&SOLUTIONTYPE=Sole";
			$nvp_str .= "&LANDINGPAGE=Billing";
		}
		
		// color and image customization was returning "Bad Request" errors on Paypal
		//if ($hdr_img) {
		//	$nvp_str .= "&HDRIMG=" . urlencode($hdr_img);
		//}
		//$nvp_str .= "&HDRBORDERCOLOR=" . $hdr_img_bordercolor;
		//$nvp_str .= "&HDRBACKCOLOR=" . $hdr_bgcolor;
		//$nvp_str .= "&PAYFLOWCOLOR=" . $page_bgcolor;
		
		$parsed_response = $this->postToPaypal('SetExpressCheckout', $nvp_str);
		if (!$parsed_response) {
			$this->setErrorMessage('SetExpressCheckout failed: ' . $this->getErrorMessage());
			return false;
		} else {
			// Redirect to paypal.com.
			$token = urldecode($parsed_response["TOKEN"]);
			$payPalURL = $this->paypal_base_url . "_express-checkout&token=$token";
			header("Location: $payPalURL");
			exit;
		}
	}
	
	public function getExpressCheckout() {
		if ($this->token) {
			$nvp_str = "&TOKEN=" . $this->token;
			$parsed_response = $this->postToPaypal('GetExpressCheckoutDetails', $nvp_str);
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
			$nvp_str = "&TOKEN=" . $this->token;
			$nvp_str .= "&PAYERID=" . $token_details['PAYERID'];
			$nvp_str .= "&PAYMENTREQUEST_0_PAYMENTACTION=" . $payment_type;
			$nvp_str .= "&PAYMENTREQUEST_0_AMT=" . $token_details['PAYMENTREQUEST_0_AMT'];
			$nvp_str .= "&PAYMENTREQUEST_0_CURRENCYCODE=" . $token_details['PAYMENTREQUEST_0_CURRENCYCODE'];
			$nvp_str .= "&PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD=InstantPaymentOnly";
			
			$parsed_response = $this->postToPaypal('DoExpressCheckoutPayment', $nvp_str);
			
			if (!$parsed_response) {
				$this->setErrorMessage('DoExpressCheckout failed: ' . $this->getErrorMessage());
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
		
		$nvp_str = "&TRANSACTIONID=" . $transaction_id;
		$nvp_str .= "&REFUNDTYPE=" . $refund_type;
		$nvp_str .= "&CURRENCYCODE=" . $currency_id;

		if($memo) {
			$nvp_str .= "&NOTE=" . $memo;
		}
		
		if (!$fullrefund) {
			if(!isset($refund_amount)) {
				$this->setErrorMessage('Partial Refund: must specify amount.');
				return false;
			} else {
		 		$nvp_str .= "&AMT=" . $refund_amount;
			}

			if(!$memo) {
				$this->setErrorMessage('Partial Refund: must specify memo.');
				return false;
			}
		}
		
		$parsed_response = $this->postToPaypal('RefundTransaction', $nvp_str);

		if (!$parsed_response) {
			$this->setErrorMessage('RefundTransaction failed: ' . $this->getErrorMessage());
			return false;
		}
	}
} // END class 
?>