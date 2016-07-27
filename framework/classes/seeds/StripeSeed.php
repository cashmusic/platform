<?php
/**
 * The StripeSeed class speaks to the Stripe API.
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2016, CASH Music
 * Licensed under the GNU Lesser General Public License version 3.
 * See http://www.gnu.org/licenses/lgpl-3.0.html
 *
 **/

class StripeSeed extends SeedBase
{
    protected $client_id, $client_secret, $error_message;
    public $publishable_key, $redirects;

    /**
     * StripeSeed constructor.
     * @param $user_id
     * @param $connection_id
     */
    public function __construct($user_id, $connection_id)
    {
        $this->settings_type = 'com.stripe';
        $this->user_id = $user_id;
        $this->connection_id = $connection_id;
        $this->redirects = false;

        if ($this->getCASHConnection()) {

            $connections = CASHSystem::getSystemSettings('system_connections');
            if (isset($connections['com.stripe'])) {
               $this->client_id = $connections['com.stripe']['client_id'];
               $this->client_secret = $connections['com.stripe']['client_secret'];
               $this->publishable_key = $connections['com.stripe']['publishable_key'];
            }
            $this->access_token = $this->settings->getSetting('access_token');
            $this->stripe_account_id = $this->settings->getSetting('stripe_account_id');

            if (CASH_DEBUG) {
               error_log(
                  'Initiated StripeSeed with: '
                  . '$this->client_id='            . (string)$this->client_id
                  . ', $this->client_secret='      . (string)$this->client_secret
                  . ', $this->publishable_key='    . (string)$this->publishable_key
                  . ', $this->access_token='       . (string)$this->access_token
                  . ', $this->stripe_account_id='  . (string)$this->stripe_account_id
               );
            }

            \Stripe\Stripe::setApiKey($this->client_secret);
        } else {
            $this->error_message = 'could not get connection settings';
        }
    }

    /**
     * @param bool $data
     * @return string
     */
    public static function getRedirectMarkup($data = false)
    {
        $connections = CASHSystem::getSystemSettings('system_connections');
        if (isset($connections['com.stripe'])) {

            $redirect_uri = CASH_ADMIN_URL . '/settings/connections/add/com.stripe/finalize';

            $client = new \AdamPaterson\OAuth2\Client\Provider\Stripe(
                array(
                    'clientId'          => $connections['com.stripe']['client_id'],
                    'clientSecret'      => $connections['com.stripe']['client_secret'],
                    'redirectUri'       => $redirect_uri,
                )
            );

            $auth_url = $client->getAuthorizationUrl();

            $return_markup = '<h4>Stripe</h4>'
                . '<p>This will redirect you to a secure login at Stripe and bring you right back. Note that you\'ll need a CASH page or secure site (https) to sell using Stripe. <a href="https://stripe.com/docs/security/ssl" target="_blank">Read more.</a></p>'
                . '<br /><br /><a href="' . $auth_url . '&redirect_uri=' . $redirect_uri.'" class="button">Connect with Stripe</a>';
            return $return_markup;
        } else {
            return 'Please add default stripe api credentials.';
        }
    }

    /**
     * This method is used during the charge process. It is used after receiving token generated from the Stripe Checkout Javascript.
     * It will send the token to Stripe to exchange for its information. Such information will be used throughout the charge process
     * (such as, create new user).
     *
     * This happens before the actual charge occurs.
     * @return bool|Stripe\Token
     */
    public function getTokenInformation()
    {

        if ($this->token) {

            \Stripe\Stripe::setApiKey($this->access_token);
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
    public static function handleRedirectReturn($data = false)
    {
        if (isset($data['code'])) {
            $connections = CASHSystem::getSystemSettings('system_connections');
            if (isset($connections['com.stripe'])) {
                //exchange the returned code for user credentials.
                $credentials = StripeSeed::getOAuthCredentials($data['code'],
                    $connections['com.stripe']['client_id'],
                    $connections['com.stripe']['client_secret']);

                if (isset($credentials['refresh_token'])) {
//                    //get the user information from the returned credentials.
//                    $user_info = StripeSeed::getUserInfo($credentials['access']);
                    //create new connection and add it to the database.
                    $new_connection = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));


                    $result = $new_connection->setSettings(
                        $credentials['stripe_user_id'] . " (Stripe)",
                        'com.stripe',
                        array(
                            'access_token' => $credentials['access_token'],
                            'publishable_key' => $credentials['stripe_publishable_key'],
                            'stripe_account_id' => $credentials['stripe_user_id']
                        )
                    );

                    if ($result) {
                        return array(
         						'id' => $result,
         						'name' => $credentials['stripe_user_id'] . ' (Stripe)',
         						'type' => 'com.stripe'
         					);
                    } else {
                        AdminHelper::formFailure('Error. Could not save connection.', '/settings/connections/');
                        return false;
                    }
                } else {
                    AdminHelper::formFailure('There was an error with the default Stripe app credentials');
                    return false;
                }
            } else {
                AdminHelper::formFailure('Please add default stripe app credentials.');
                return false;
            }
        } else {
            AdminHelper::formFailure('There was an error. (session) Please try again.');
            return false;
        }
    }

    /**
     *
     * This method is used to exchange the returned code from Stripe with Stripe again to get the user credentials during
     * the authentication process.
     *
     * Exchange an authorization code for OAuth 2.0 credentials.
     *
     * @param String $authorization_code Authorization code to exchange for OAuth 2.0 credentials.
     * @param $client_id
     * @param $client_secret
     * @return String Json representation of the OAuth 2.0 credentials.
     */
    public static function getOAuthCredentials($authorization_code, $client_id, $client_secret)
    {
        try {
            $client = new \AdamPaterson\OAuth2\Client\Provider\Stripe(
                array(
                'clientId'          => $client_id,
                'clientSecret'      => $client_secret
                )
            );

            $token = $client->getAccessToken('authorization_code', array(
                'code' => $authorization_code
            )
            );

            $token_values = $token->getValues();

            if (!empty($token_values)) {
                return array(
                    'access_token' => $token->access_token,
                    'refresh_token' => $token->refresh_token,
                    'stripe_publishable_key' => $token->stripe_publishable_key,
                    'stripe_user_id' => $token->stripe_user_id
                );
            }

            return false;

        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * This method makes use of Stripe library in getting the user information from the returned credentials during
     * the authentication process.
     *
     * @param $credentials
     * @return Stripe\Account
     */
    public static function getUserInfo($credentials)
    {
        //require_once(CASH_PLATFORM_ROOT.'/lib/stripe/lib/Stripe.php');
        \Stripe\Stripe::setApiKey($credentials);

        $user_info = \Stripe\Account::retrieve();
        return $user_info;
    }

    /**
     * @param $msg
     */
    protected function setErrorMessage($msg)
    {
        $this->error_message = $msg;
        if (CASH_DEBUG) {
          error_log($this->error_message);
       }
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * We don't need this for Stripe, since the checkout.js library handles it. Let's leave it for consistency across seeds, though
     *
     * @return bool
     */
    public function preparePayment() {
        return false;
    }

    /**
     * Fired from finalizeRedirectedPayment, in CommercePlant. Sends the actual charge and seedToken to the Stripe API—
     * this is really where almost everything happens for StripeSeed charges.
     *
     * @param $total_price
     * @param $description
     * @param $token
     * @param $email_address
     * @param $customer_name
     * @param $shipping_info
     * @return array|bool
     */
    public function doPayment($total_price, $description, $token, $email_address=false, $customer_name=false, $currency='USD') {

      if (CASH_DEBUG) {
         error_log(
            'Called StripeSeed::doPayment with: '
            . '$total_price='       . (string)$total_price
            . ', $description='     . (string)$description
            . ', $token='           . (string)$token
            . ', $email_address='   . (string)$email_address
            . ', $customer_name='   . (string)$customer_name
            . ', $currency='        . (string)$currency
         );
      }

    if (!empty($token)) {

        try {
            \Stripe\Stripe::setApiKey($this->client_secret);

            if (!$payment_results = \Stripe\Charge::create(
                array(
                    "amount" => ($total_price * 100),
                    "currency" => $currency,
                    "source" => $token, // obtained with Stripe.js
                    "description" => $description
                ),
                array(
                   "stripe_account" => $this->stripe_account_id
                ) // stripe connect, charge goes to oauth user instead of cash
            )) {
                $this->setErrorMessage("In StripeSeed::doPayment. Stripe payment failed.");
                return false;
            }
            } catch (\Stripe\Error\Card $e) {
               $this->setErrorMessage("In StripeSeed::doPayment. " . $e->getMessage());
               return false;
            } catch (\Stripe\Error\InvalidRequest $e) {
               $this->setErrorMessage("In StripeSeed::doPayment. " . $e->getMessage());
               return false;
            } catch (\Stripe\Error\Authentication $e) {
               $this->setErrorMessage("In StripeSeed::doPayment. " . $e->getMessage());
               return false;
            } catch (\Stripe\Error\ApiConnection $e) {
               $this->setErrorMessage("In StripeSeed::doPayment. " . $e->getMessage());
               return false;
            } catch (\Stripe\Error\Base $e) {
               $this->setErrorMessage("In StripeSeed::doPayment. " . $e->getMessage());
               return false;
            } catch (Exception $e) {
               $this->setErrorMessage("In StripeSeed::doPayment. There was an issue with your Stripe API request. Exception: " . json_encode($e));
               return false;
            }


        } else {
            $this->setErrorMessage("In StripeSeed::doPayment. No Stripe token found.");
            return false;
        }

        // check if Stripe charge was successful

        if ($payment_results->status == "succeeded") {

            // look up the transaction fees taken off the top, for record
            $transaction_fees = \Stripe\BalanceTransaction::retrieve($payment_results->balance_transaction,
                array("stripe_account" => $this->stripe_account_id));
            // we can actually use the BalanceTransaction::retrieve method as verification that the charge has been placed
            if (!$transaction_fees) {
                error_log("Balance transaction failed, is this a valid charge?");
                $this->setErrorMessage("In StripeSeed::doPayment. Balance transaction failed, is this a valid charge?");
                return false;
            }

            $full_name = explode(' ', $customer_name, 2);
            // nested array for data received, standard across seeds
            $order_details = array(
                'transaction_description' => '',
                'customer_email' => $email_address,
                'customer_first_name' => $full_name[0],
                'customer_last_name' => $full_name[1],
                'customer_name' => $customer_name,

                'customer_phone' => '',
                'transaction_date' => $payment_results->created,
                'transaction_id' => $payment_results->id,
                'sale_id' => $payment_results->id,
                'items' => array(),
                'total' => round($payment_results->amount / 100),
                'other_charges' => array(),
                'transaction_fees' => ($transaction_fees->fee / 100),
                'refund_url' => $payment_results->refunds->url,
                'status' => "complete"
            );

            return array('total' => round($payment_results->amount / 100),
                'customer_email' => $email_address,
                'customer_first_name' => $full_name[0],
                'customer_last_name' => $full_name[1],
                'customer_name' => $customer_name,

                'timestamp' => $payment_results->created,
                'transaction_id' => $payment_results->id,
                'service_transaction_id' => $payment_results->id,
                'service_charge_id' => $payment_results->balance_transaction,
                'service_fee' => ($transaction_fees->fee / 100),
                'order_details' => $order_details
            );
        } else {

            $this->setErrorMessage("In StripeSeed::doPayment. Error with Stripe payment.");
            return false;
        }

    }


    /**
     * Fired from cancelOrder, in CommercePlant. Sends charge token to the Stripe API with our client secret in order to do full refund.
     *
     * @param $sale_id
     * @param int $refund_amount
     * @param string $currency_id
     * @return bool|\Stripe\Refund
     */
    public function refundPayment($sale_id, $refund_amount = 0, $currency_id = 'USD')
    {

        // try to contact the stripe API for refund, or fail gracefully
        try {
            \Stripe\Stripe::setApiKey($this->client_secret);

            $refund_response = \Stripe\Refund::create(array(
                "charge" => $sale_id
            ),array("stripe_account" => $this->stripe_account_id));
        } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            $body = $e->getJsonBody();
            $this->setErrorMessage("In StripeSeed::refundPayment. Stripe API rate limit exceeded: " . $body['error']['message']);
            return false;

        } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            $body = $e->getJsonBody();
            $this->setErrorMessage("In StripeSeed::refundPayment. Invalid Stripe refund request: " . $body['error']['message']);
            return false;

        } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            $body = $e->getJsonBody();
            $this->setErrorMessage("In StripeSeed::refundPayment. Could not authenticate Stripe: " . $body['error']['message']);
            return false;

        } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            $body = $e->getJsonBody();
            $this->setErrorMessage("In StripeSeed::refundPayment. Could not communicate with Stripe API: " . $body['error']['message']);
            return false;

        } catch (\Stripe\Error\Base $e) {
            // Display a very generic error to the user, and maybe send
            // yourself an email
            $body = $e->getJsonBody();
            $this->setErrorMessage("In StripeSeed::refundPayment. General Stripe error: " . $body['error']['message']);
            return false;

        } catch (Exception $e) {
            // Something else happened, completely unrelated to Stripe
            $body = $e->getJsonBody();
            $this->setErrorMessage("In StripeSeed::refundPayment. Something went wrong: " . $body['error']['message']);
            return false;

        }

        // let's make sure that the object returned is a successful refund object
        if ($refund_response->object == "refund") {
            return $refund_response;
        } else {
            $this->setErrorMessage("In StripeSeed::refundPayment. Something went wrong while issuing this refund.");
            return false;
        }


    }
} // END class
