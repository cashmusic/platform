<?php
/**
 * The StripeSeed class speaks to the Stripe API.
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

require CASH_PLATFORM_ROOT  . '/lib/stripe/StripeOAuth.class.php';
require CASH_PLATFORM_ROOT	. '/lib/stripe/init.php';
//require CASH_PLATFORM_ROOT  . '/lib/stripe/StripeOAuth2Client.class.php';

class StripeSeed extends SeedBase {
	protected $client_id, $client_secret, $error_message;
	public $publishable_key;

	public function __construct($user_id, $connection_id) {
		$this->settings_type = 'com.stripe';
		$this->user_id = $user_id;
		$this->connection_id = $connection_id;

		if ($this->getCASHConnection()) {

			$this->client_id  = $this->settings->getSetting('client_id');
			$this->client_secret = $this->settings->getSetting('client_secret');
			$this->publishable_key = $this->settings->getSetting('publishable_key');
			$this->access_token = $this->settings->getSetting('access_token');
			$sandboxed           = $this->settings->getSetting('sandboxed');

			\Stripe\Stripe::setApiKey($this->client_secret);

			if (!$this->client_id || !$this->client_secret || !$this->publishable_key) {
				$connections = CASHSystem::getSystemSettings('system_connections');

				if (isset($connections['com.stripe'])) {
					$this->client_id     = $connections['com.stripe']['client_id'];
					$this->client_secret   = $connections['com.stripe']['client_secret'];
					$this->publishable_key  	   = $connections['com.stripe']['publishable_key'];
					$sandboxed         = $connections['com.stripe']['sandboxed'];

					if ($sandboxed) {
						//sandboxed
					}
				}
			}
		} else {
			$this->error_message = 'could not get connection settings';
		}
	}

	public static function getRedirectMarkup($data=false) {
		$connections = CASHSystem::getSystemSettings('system_connections');
		if (isset($connections['com.stripe'])) {
			$login_url = StripeSeed::getAuthorizationUrl($connections['com.stripe']['client_id'], $connections['com.stripe']['client_secret']);
			$return_markup = '<h4>Stripe</h4>'
				. '<p>This will redirect you to a secure login at Stripe and bring you right back.</p>'
				. '<a href="' . $login_url . '&redirect_uri=http://localhost:8888'. ADMIN_WWW_BASE_PATH . '/settings/connections/add/com.stripe/finalize" class="button">Connect with Stripe</a>';
			return $return_markup;
		} else {
			return 'Please add default stripe api credentials.';
		}
	}

	public static function getAuthorizationUrl($client_id,$client_secret) {

		$client = new StripeOAuth($client_id, $client_secret);
		$auth_url = $client->getAuthorizeUri();
		return $auth_url;
	}



	/*
     * This method is used during the charge process. It is used after receiving token generated from the Stripe Checkout Javascript.
     * It will send the token to Stripe to exchange for its information. Such information will be used throughout the charge process (such as, create new user).
     * This happens before the actual charge occurs.
     */

	public function getTokenInformation() {

		if ($this->token) {

			Stripe::setApiKey($this->access_token);
			$tokenInfo = \Stripe\Token::retrieve($this->token);
			if (!$tokenInfo) {
				$this->setErrorMessage('getTokenInformation failed: ' . $this->getErrorMessage());
				return false;
			} else {
				return $tokenInfo;
			}
		} else {
			$this->setErrorMessage("Token is Missing!");
			return false;
		}
	}


	/**
	 * handleRedirectReturn
	 * Handles redirect from API Auth for service
	 * @param bool|false $data
	 * @return string
     */
	public static function handleRedirectReturn($data=false) {
		if (isset($data['code'])) {
			$connections = CASHSystem::getSystemSettings('system_connections');
			if (isset($connections['com.stripe'])) {
				//exchange the returned code for user credentials.
				$credentials = StripeSeed::exchangeCode($data['code'],
					$connections['com.stripe']['client_id'],
					$connections['com.stripe']['client_secret']);

				if (isset($credentials['refresh'])) {
					//get the user information from the returned credentials.
					$user_info = StripeSeed::getUserInfo($credentials['access']);
					//create new connection and add it to the database.
					$new_connection = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));


					$result = $new_connection->setSettings(
						$credentials['userid'] . " (Stripe)",
						'com.stripe',
						array(
							'access_token'   => $credentials['access'],
							'publishable_key' => $credentials['publish'],
							'user_id' => $credentials['userid']
						)
					);

					if ($result) {
						AdminHelper::formSuccess('Success. Connection added. You\'ll see it in your list of connections.','/settings/connections/');
					} else {
						AdminHelper::formFailure('Error. Could not save connection.','/settings/connections/');
					}
				}else{
					return 'Could not find a refresh token from Stripe';
				}
			} else {
				return 'Please add default stripe app credentials.';
			}
		} else {
			return 'There was an error. (session) Please try again.';
		}
	}

	/**
	 *
	 * This method is used to exchange the returned code from Stripe with Stripe again to get the user credentials during the authentication process.
	 *
	 * Exchange an authorization code for OAuth 2.0 credentials.
	 *
	 * @param String $authorization_code Authorization code to exchange for OAuth 2.0 credentials.
	 * @return String Json representation of the OAuth 2.0 credentials.
	 */
	public static function exchangeCode($authorization_code,$client_id,$client_secret) {
		require_once(CASH_PLATFORM_ROOT.'/lib/stripe/StripeOAuth.class.php');
		try {
			$client = new StripeOAuth($client_id, $client_secret);
			$token =  $client->getTokens($authorization_code);
			$publishable = array(
				'publish' => $client->getPublishableKey(),
				'userid' => $client->getUserId()
			);
			return array_merge($token, $publishable);
		} catch (Exception $e) {
			return false;
		}
	}

	/*
 * This method makes use of Stripe library in getting the user information from the returned credentials during the authentication process.
 */
	public static function getUserInfo($credentials) {
		//require_once(CASH_PLATFORM_ROOT.'/lib/stripe/lib/Stripe.php');
		Stripe::setApiKey($credentials);

		$user_info = \Stripe\Account::retrieve();
		return $user_info;
	}

	protected function setErrorMessage($msg) {
		$this->error_message = $msg;
	}

	public function getErrorMessage() {
		return $this->error_message;
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

		/*echo "StripeSeed::setCheckout";

		$amount = new Amount();
		$amount->setCurrency($currency_id)
			->setTotal($payment_amount);

		error_log("shipping + ". $shipping_amount);
		if ($request_shipping_info && $shipping_amount > 0) {
			$shipping = new Details();
			$shipping->setShipping($shipping_amount)
				//->setTax(1.3)
				->setSubtotal($payment_amount - $shipping_amount);
				//TODO: assumes shipping cost is passed in as part of the total $payment_amount

			$amount->setDetails($shipping);
		}

		$transaction = new Transaction();
		$transaction->setAmount($amount)
			->setDescription($ordername)
			->setInvoiceNumber($ordersku."farts");

		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl($return_url."&success=true")
					 ->setCancelUrl($cancel_url."&success=false");


		$payment = new Payment();
		$payment->setIntent($payment_type)
			->setPayer($payer)
			->setRedirectUrls($redirectUrls)
			->setTransactions(array($transaction));


		try { $payment->create($this->api_context); } catch (Exception $ex) {

			$error = json_decode($ex->getData());
			$this->setErrorMessage($error->message);
		}

		$approval_url = $payment->getApprovalLink();

		if (!empty($approval_url)) {
			return array(
				'redirect_url' => $approval_url,
				'data_sent' => json_encode($payment->getTransactions() )
			);
		} else {
			// approval link isn't set, return to page and post error
			$this->setErrorMessage('There was an error contacting PayPal for this payment.');
		}
		return true;*/

        if (!empty($return_url)) {
            return array(
                'redirect_url' => $return_url."&success=true",
                'data_sent'    => ""
            );
        } else {
            // approval link isn't set, return to page and post error
            $this->setErrorMessage('There was an error contacting Stripe for this payment.');
        }

        return true;
	}

	public function getCheckout() {

        // Stripe.js pretty much handled all the fun stuff.
		// TODO: Get token
		// TODO: Do charge
		error_log(print_r($_GET, true));
		//TODO: remember to generalize the token GET var

		\Stripe\Stripe::setApiKey($this->client_secret);

		if (!empty($_GET['stripeToken'])) {

			if (!$payment_results = \Stripe\Charge::create(
				array(
				"amount" => 400,
				"currency" => "usd",
				"source" => $_GET['stripeToken'], // obtained with Stripe.js
				"description" => "Charge for test@example.com"
				)
				)
			) {

				$this->setErrorMessage("Stripe payment failed.");
				return false;
			}
		} else {
			$this->setErrorMessage("No token found.");
			return false;
		}

		// TODO: Get transaction from Stripe
		// TODO: Pass transaction results and standardize
		//

		// Let's keep the success boolean here even though it's redundant in this case
		if ($payment_results['status'] == "succeeded") {

			// nested array for data received, standard across seeds
			//TODO: this is set for single item transactions for now; should be expanded for cart transactions

			$order_details = array(
				'transaction_description' => '',
				'customer_email' => $_GET['email'],
				'customer_first_name' => '',
				'customer_last_name' => '',
				'customer_name' => '',
				'customer_shipping_name' => '',
				'customer_address1' => '',
				'customer_address2' => '',
				'customer_city' => '',
				'customer_region' => '',
				'customer_postalcode' => '',
				'customer_country' => '',
				'customer_countrycode' => '',
				'customer_phone' => '',
				/* 																*/
				'transaction_date' 	=> strtotime($payment_results->created),
				'transaction_id' 	=> $payment_results->id,
				'sale_id'			=> $payment_results->balance_transaction,
				'items' 			=> array(),
				'total' 			=> $payment_results->amount,
				'other_charges' 	=> array(),
				'transaction_fees'  => 0,
				);

			$payer_info = array("payer" => array(
				"first_name" => "",
				"last_name" => "",
				"email" => $_GET['email'],
				"country_code" => ""

			)
			);


			return array('total' => $payment_results->amount,
						'payer' => $payer_info,
						'timestamp' => strtotime($payment_results->created),
						'transaction_id' => $payment_results->id,
						'transaction_fee' => 0,
						'order_details' => json_encode($payment_results)
						);
		} else {
			return false;
		}

	}

	public function doRefund($sale_id,$refund_amount=0,$currency_id='USD') {

		$amt = new Amount();
		$amt->setCurrency($currency_id);
		$amt->setTotal($refund_amount);

		$refund = new Refund();
		$refund->setAmount($amt);

		$sale = new Sale();
		$sale->setId($sale_id);

		$refund_response = $sale->refund($refund, $this->api_context);

		if (!$refund_response) {
			$this->setErrorMessage('RefundTransaction failed: ' . $this->getErrorMessage());
			error_log($this->getErrorMessage());
			return false;
		} else {
			return $refund_response;
		}

	}

	/**
	 * getTransactionByData
	 *
	 * Seed specific method to get transaction details for a payment service
	 *
	 * @param $token
	 * @return array
	 */

	public function setTransactionByToken($amount, $currency, $token, $description) {

		// Create the charge on Stripe's servers - this will charge the user's card
		try {

			$result = \Stripe\Charge::create(array(
				"amount" => 1000, // amount in cents, again
				"currency" => "usd",
				"source" => $token,
				"description" => "Example charge"
			));

			error_log("RESULT ". print_r($result, true));
		} catch(\Stripe\Error\Card $e) {
			error_log("ERROR ".print_r($e, true));
			return false;
		}

		return $result;
	}
} // END class
?>