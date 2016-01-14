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

                    //TODO: We want to add test/sandbox credentials to the JSON, so we can just set them here instead
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

    public function preparePayment(
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

        // there's not a whole lot to do in this method for Stripe. let's make sure we have all the params we need to make the transaction spin, and return them to initiatePaymentRedirect.

        if (!empty($_POST['email'])) {
            $return_url .= '&email='.$_POST['email'];
        }

        if (!empty($_POST['connection_id'])) {
            $return_url .= '&connection_id='.$_POST['connection_id'];
        }

        if (!empty($_POST['connection_id'])) {
            $return_url .= '&connection_id='.$_POST['connection_id'];
        }

        if (!empty($_POST['stripeToken'])) {
            $return_url .= '&stripeToken='.$_POST['stripeToken'];
        }

        // not a whole lot we can check on at this point, so let's just make sure the token is set.
        if (!empty($_POST['stripeToken'])) {
            return array(
                'redirect_url' => $return_url."&success=true",
                'data_sent'    => ""
            );
        } else {
            // approval link isn't set, return to page and post error
            $this->setErrorMessage('There was an error contacting Stripe for this payment.');
            return array(
                'redirect_url' => $return_url."&success=false",
                'data_sent'    => ""
            );

        }
    }

    public function doPayment($transaction = "") {

        // we need to get the details of the order to pass in the amount to Stripe
        $order_details = json_decode($transaction['order_contents']);
        $order_details = $order_details[0];

        \Stripe\Stripe::setApiKey($this->client_secret);

        if (!empty($_GET['stripeToken'])) {

            if (!$payment_results = \Stripe\Charge::create(
                array(
                    "amount" => ($order_details->price*100),
                    "currency" => "usd",
                    "source" => $_GET['stripeToken'], // obtained with Stripe.js
                    "description" => $order_details->description
                )
            )
            ) {

                $this->setErrorMessage("Stripe payment failed.");
                return false;
            }
        } else {
            $this->setErrorMessage("No Stripe token found.");
            return false;
        }

        // check if Stripe charge was successful
        if ($payment_results->status == "succeeded") {

            // look up the transaction fees taken off the top, for record
            $transaction_fees = \Stripe\BalanceTransaction::retrieve($payment_results->balance_transaction);

            // we can actually use the BalanceTransaction::retrieve method as verification that the charge has been placed
            if (!$transaction_fees) {
                $this->setErrorMessage("Balance transaction failed, is this a valid charge?");
                return false;
            }

            // nested array for data received, standard across seeds
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
                'transaction_date' 	=> $payment_results->created,
                'transaction_id' 	=> $payment_results->balance_transaction,
                'sale_id'			=> $payment_results->id,
                'items' 			=> array(),
                'total' 			=> round($payment_results->amount/100),
                'other_charges' 	=> array(),
                'transaction_fees'  => ($transaction_fees->fee/100),
                'refund_url'        => $payment_results->refunds->url,
                'status'            => "complete"
            );

            //TODO: this is set for single item transactions for now; should be expanded for cart transactions

            $payer_info = array(
                "first_name" => "",
                "last_name" => "",
                "email" => $_GET['email'],
                "country_code" => "");


            return array('total' => round($payment_results->amount/100),
                'payer' => $payer_info,
                'timestamp' => $payment_results->created,
                'transaction_id' => $payment_results->id,
                'transaction_fee' => ($transaction_fees->fee/100),
                'order_details' => json_encode($order_details)
            );
        } else {

            $this->setErrorMessage("Error with doPayment");
            return false;
        }

    }

    public function doRefund($sale_id,$refund_amount=0,$currency_id='USD') {

        \Stripe\Stripe::setApiKey($this->client_secret);

        $refund_response = \Stripe\Refund::create(array(
            "charge" => $sale_id
        ));

        if (!$refund_response || $refund_response->object != "refund") {
            $this->setErrorMessage('Refund Transaction failed ');
            error_log(print_r($refund_response, true));
            return false;
        } else {
            error_log(print_r($refund_response, true));
            return $refund_response;
        }

    }
} // END class
?>