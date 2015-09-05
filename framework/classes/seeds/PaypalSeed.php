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
 * Copyright (c) 2013, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 *
 * This file is generously sponsored by Justin Miranda
 *
 **/
class PaypalSeed extends SeedBase {
	protected $api_username, $api_password, $api_signature, $api_endpoint, $api_version, $paypal_base_url, $error_message, $token;
	protected $merchant_email = false;

	public function __construct($user_id, $connection_id, $token=false) {
		$this->settings_type = 'com.mailchimp';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;
		if ($this->getCASHConnection()) {
			$this->api_version   = '94.0';
			$this->api_username  = $this->settings->getSetting('username');
			$this->api_password  = $this->settings->getSetting('password');
			$this->api_signature = $this->settings->getSetting('signature');
			$sandboxed           = $this->settings->getSetting('sandboxed');

			if (!$this->api_username || !$this->api_password || !$this->api_signature) {
				$connections = CASHSystem::getSystemSettings('system_connections');
				if (isset($connections['com.paypal'])) {
					$this->merchant_email = $this->settings->getSetting('merchant_email'); // present in multi
					$this->api_username   = $connections['com.paypal']['username'];
					$this->api_password   = $connections['com.paypal']['password'];
					$this->api_signature  = $connections['com.paypal']['signature'];
					$sandboxed            = $connections['com.paypal']['sandboxed'];
				}
			}

			$this->token = $token;

			$this->api_endpoint = "https://api-3t.paypal.com/nvp";
			$this->paypal_base_url = "https://www.paypal.com/webscr&cmd=";
			if ($sandboxed) {
				$this->api_endpoint = "https://api-3t.sandbox.paypal.com/nvp";
				$this->paypal_base_url = "https://www.sandbox.paypal.com/webscr&cmd=";
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
		$invoice=false,
		$shipping_amount=false
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
		if (!$request_shipping_info) {
			$nvp_parameters['NOSHIPPING'] = 1;
		}
		if ($shipping_amount) {
			$nvp_parameters['NOSHIPPING'] = 0;
			$nvp_parameters['L_PAYMENTREQUEST_0_AMT0'] = $payment_amount-$shipping_amount;
			$nvp_parameters['PAYMENTREQUEST_0_ITEMAMT'] = $payment_amount-$shipping_amount;
			$nvp_parameters['PAYMENTREQUEST_0_SHIPPINGAMT'] = $shipping_amount;
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
			error_log($this->getErrorMessage());
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
