<?php
/**
 * This class is responsible to speak to Stripe API.
 * It basically provides function for authentication and charging money.
 * There are also some functions that will not be used for now. Butm\, they might be beneficial in the future.
 *
 **/
class StripeSeed extends SeedBase {
    protected $error_message, $token, $client_id, $redirect_uri, $client_secret,$publishable_key, $access_token;

    public function __construct($user_id, $connection_id, $token=false) {
        $this->settings_type = 'com.stripe';
        $this->user_id = $user_id;
        $this->connection_id = $connection_id;
        if ($this->getCASHConnection()) {
            $this->client_id  = $this->settings->getSetting('client_id');
            $this->redirect_uri  = $this->settings->getSetting('redirect_uri');
            $this->client_secret = $this->settings->getSetting('client_secret');
            $this->publishable_key = $this->settings->getSetting('publishable_key');
            $this->access_token = $this->settings->getSetting('access_token');
            if (!$this->client_id || !$this->redirect_uri || !$this->client_secret) {
                $connections = CASHSystem::getSystemSettings('system_connections');
                if (isset($connections['com.stripe'])) {
                    $this->client_id   = $connections['com.stripe']['client_id'];
                    $this->redirect_uri   = $connections['com.stripe']['redirect_uri'];
                    $this->client_secret  = $connections['com.stripe']['client_secret'];
                }
            }

            $this->token = $token;

        } else {
            $this->error_message = 'could not get connection settings';
        }
    }

    /*
     * This method is responsible for checking for CM's Stripe Api credentials defined in "/framework/settings/connections.json" before proceeding to the authentication through Stripe Website.
     * It then gets the Stripe URL from the getAuthorizationUrl method.
     *
     */
    public static function getRedirectMarkup($data=false) {
        $connections = CASHSystem::getSystemSettings('system_connections');
        if (isset($connections['com.stripe'])) {
            $login_url = StripeSeed::getAuthorizationUrl($connections['com.stripe']['client_id'],$connections['com.stripe']['client_secret']);
            $return_markup = '<h4>Stripe</h4>'
                . '<p>This will redirect you to a secure login at Stripe and bring you right back.</p>'
                . '<a href="' . $login_url . '" class="button">Connect your Stripe</a>';
            return $return_markup;
        } else {
            return 'Please add default stripe api credentials.';
        }
    }

    /*
     * This method is used to handle the response back from Stripe during the authentication process.
     * After it receives the code back, it then sent the code to Stripe again through method "exchangeCode" to get the user credentitals.
     * It then uses the returned user credentials to get the user information and saves them to the DB.
     *
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
                        $user_info['email'] . ' (Stripe)',
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

    protected function setErrorMessage($msg) {
        $this->error_message = $msg;
    }

    public function getErrorMessage() {
        return $this->error_message;
    }

    /*
     * This method makes use of Stripe library in getting the user information from the returned credentials during the authentication process.
     */
    public static function getUserInfo($credentials) {
        require_once(CASH_PLATFORM_ROOT.'/lib/stripe/Stripe.php');
        Stripe::setApiKey($credentials);
        $user_info = Stripe_Account::retrieve();
        return $user_info;
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
     * This method is used to get the URL to redirect user to Stripe website in the OAuth process.
     */

    public static function getAuthorizationUrl($client_id,$client_secret) {
        require_once(CASH_PLATFORM_ROOT.'/lib/stripe/StripeOAuth.class.php');
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
            require_once(CASH_PLATFORM_ROOT.'/lib/stripe/Stripe.php');
            Stripe::setApiKey($this->access_token);
            $tokenInfo = Stripe_Token::retrieve($this->token);
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


    /*
     * The actual charge occurs with the use of this method.
     * There are also some error handlings implemented in this method.
     */

    public function doCharge($amount, $currency, $charge_description) {
        $return_array = array();
        if ($this->token) {
            try
            {
                require_once(CASH_PLATFORM_ROOT.'/lib/stripe/Stripe.php');
                Stripe::setApiKey($this->access_token);
                $charge = Stripe_Charge::create(array(
                    "amount" => $amount * 100,
                    "currency" => $currency ,
                    "source" => $this->token, // obtained from Stripe.js
                    "description" => $charge_description
                ));

                $return_array = $charge;


            } catch(Stripe_CardError $e) {
                $body = $e->getJsonBody();
                $err  = $body['error'];
                $return_array['status'] = $err['code'];

            } catch(Stripe_InvalidRequestError $e) {
                $return_array['status'] = "invalid request";

            } catch(Stripe_AuthenticationError $e) {
                $return_array['status'] = "authentication error";

            } catch(Stripe_ApiConnectionError $e) {
                $return_array['status'] = "api connection error";

            } catch(Stripe_Error $e) {
                $return_array['status'] = "stripe base error";

            } catch(Exception $e) {
                $return_array['status'] = "undefined error";
            }

            return $return_array;

        } else {
            $this->setErrorMessage("No token was found.");
            $return_array['status'] = 'token missing';
            return $return_array;
        }
    }

    /*
     * This method is used to generate Json chunk containing the information required by the Stripe checkout javascript.
     * Some of the parameters are not used but sent to this method just in case we need them in the future.
     */

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

        $this->cash_base_url = CASH_PUBLIC_URL."/stripe.php";
        $pk=$this->publishable_key;
        $desc=$ordername;
        $amnt=$payment_amount*100;

        //Below was used initially to redirect user to another webpage.

        //$secretParams = "desc=$desc&pk=$pk&amnt=$amnt";
        //$encryptedSecret = Stripeseed::cryptoJsAesEncrypt("cashmusic", $secretParams);
        //$stripe_url = $this->cash_base_url . "?return_url=$return_url&cancel_url=$cancel_url&param=".base64_encode($encryptedSecret);

        $stripeParam = array(
            'amount' => $amnt,
            'pk' => $pk,
            'desc' => $desc,
            'currency' => $currency_id,
            'return_url' => $return_url
        );
        return json_encode($stripeParam);
    }
    /*
     * This isn't used anymore. It was developped initially to support encrpytion of parameters sent via the URL.
     * copy from here http://stackoverflow.com/questions/24337317/encrypt-with-php-decrypt-with-javascript-cryptojs
    */
    function cryptoJsAesEncrypt($passphrase, $value){
        $salt = openssl_random_pseudo_bytes(8);
        $salted = '';
        $dx = '';
        while (strlen($salted) < 48) {
            $dx = md5($dx.$passphrase.$salt, true);
            $salted .= $dx;
        }
        $key = substr($salted, 0, 32);
        $iv  = substr($salted, 32,16);
        $encrypted_data = openssl_encrypt(json_encode($value), 'aes-256-cbc', $key, true, $iv);
        $data = array("ct" => base64_encode($encrypted_data), "iv" => bin2hex($iv), "s" => bin2hex($salt));
        return json_encode($data);
    }



} // END class
?>