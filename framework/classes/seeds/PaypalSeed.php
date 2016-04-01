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

require_once CASH_PLATFORM_ROOT . '/lib/paypal/PPBootStrap.php';

use PayPal\Service\PermissionsService;
use PayPal\Types\Common\RequestEnvelope;
use PayPal\Types\Perm\RequestPermissionsRequest;
use PayPal\Types\Perm\GetAccessTokenRequest;
use PayPal\Auth\Oauth\AuthSignature;



class PaypalSeed extends SeedBase {
    protected $api_username, $api_password, $api_signature, $api_endpoint, $api_version, $paypal_base_url, $error_message, $token, $permissions_account, $permissions_token, $permissions_token_secret;
    protected $merchant_email = false;

    public function __construct($user_id, $connection_id, $token=false) {
        $this->settings_type = 'com.paypal';
        $this->user_id = $user_id;
        $this->connection_id = $connection_id;

        // this tells CommercePlant if we need a redirect for this seed, or if we can just head right to doPayment
        $this->redirects = true;

        if ($this->getCASHConnection()) {
            $this->api_version   = '94.0';
            $this->api_username  = $this->settings->getSetting('username');
            $this->api_password  = $this->settings->getSetting('password');
            $this->api_signature = $this->settings->getSetting('signature');
            $sandboxed           = $this->settings->getSetting('sandboxed');

            /* permissions API stuff, new implementation for refunds, mass payments, etc */
            $this->permissions_account  = $this->settings->getSetting('permissions_account');
            $this->permissions_token  = $this->settings->getSetting('permissions_token');
            $this->permissions_token_secret = $this->settings->getSetting('permissions_token_secret');

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

        // do the PayPal Permissions API funky chicken

        $url = "http://dev.localhost:8888";
        $returnURL = $url. ADMIN_WWW_BASE_PATH . '/settings/connections/add/com.paypal/finalize';
        $cancelURL = $url. "/";

        $scope = array(0=>'EXPRESS_CHECKOUT',1=>'RECURRING_PAYMENTS',2=>'REFUND', 3=>'MASS_PAY', 4=>'ACCESS_BASIC_PERSONAL_DATA');

        $requestEnvelope = new RequestEnvelope("en_US");

        $request = new RequestPermissionsRequest($scope, $returnURL);
        $request->requestEnvelope = $requestEnvelope;

        $service = new PermissionsService(Configuration::getAcctAndConfig());
        try {

            $response = $service->RequestPermissions($request);
        } catch(Exception $ex) {
            error_log( print_r($ex, true) );
        }

        $token = $response->token;

        if(strtoupper($response->responseEnvelope->ack) == 'SUCCESS') {

            $payPalURL = 'https://www.sandbox.paypal.com/webscr&cmd=_grant-permission&request_token=' . $token;

        }

        if (isset($connections['com.paypal'])) {
            $return_markup = '<h4>Paypal</h4>'
                . '<p>You\'ll need a verified Business or Premier Paypal account to connect properly. '
                . 'Those are free upgrades, so just double-check your account. You '
                . 'can learn more about what they entail <a href="https://cms.paypal.com/cgi-bin/?cmd=_render-content&content_ID=developer/EC_setup_permissions">here</a>.</p>'
                . '<a href="' . $payPalURL . '" class="button">Connect with Paypal</a>';
            return $return_markup;
        } else {
            return 'Please add default paypal api credentials.';
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
            $connections = CASHSystem::getSystemSettings('system_connections');
            if (isset($connections['com.paypal'])) {
                //exchange the returned code for user credentials.
                $requestEnvelope = new RequestEnvelope();
                $requestEnvelope->errorLanguage = "en_US";
                $request = new GetAccessTokenRequest();
                $request->requestEnvelope = $requestEnvelope;

                $request->token = $_REQUEST['request_token'];
                $request->verifier = $_REQUEST['verification_code'];

                $service = new PermissionsService(Configuration::getAcctAndConfig());
                try {
                    $response = $service->GetAccessToken($request);
                } catch (Exception $ex) {
                    AdminHelper::formSuccess("There was an error authenticating with PayPal.");
                    return false;
                }

                //create new connection and add it to the database.
                $new_connection = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));

                //TODO: get email address from API

                $authorization_header = PaypalSeed::getAuthorizationHeader(
                    $connections['com.paypal']['username'],
                    $connections['com.paypal']['password'],
                    $response->token,
                    $response->tokenSecret
                );

                error_log($authorization_header);

                $merchant_basic_info = PaypalSeed::getEmailFromPermissionsAPI($authorization_header);

                $result = $new_connection->setSettings(
                    "somedude@dude.com (PayPal)",
                    'com.paypal',
                    array(
                        'permissions_account' => "somedude@dude.com",
                        'permissions_token' => $response->token,
                        'permissions_token_secret' => $response->tokenSecret
                    )
                );

                if ($result) {
                    AdminHelper::formSuccess('Success. Connection added. You\'ll see it in your list of connections.', '/settings/connections/');
                    return true;
                } else {
                    AdminHelper::formFailure('Error. Could not save connection.', '/settings/connections/');
                    return false;
                }





            } else {
                AdminHelper::formFailure('Please add default Paypal app credentials.');
                return false;
            }

    }

    /**
     * @param $api_username
     * @param $api_password
     * @param $token
     * @param $secret
     * @return bool|string
     */
    protected static function getAuthorizationHeader($api_username, $api_password, $token, $secret) {

        $auth = new AuthSignature();
        $auth_response = $auth->genSign(
            $api_username,
            $api_password,
            $token,
            $secret,
            'POST',
            "https://api.sandbox.paypal.com/nvp"
        );

        $auth_string = "token=" . $token . ",signature=" . $auth_response['oauth_signature'] . ",timestamp=" . $auth_response['oauth_timestamp'];

        if ($auth_response) {
            return $auth_string;
        } else {
            return false;
        }
    }

    protected function setErrorMessage($msg) {
        $this->error_message = $msg;
    }

    public function getErrorMessage() {
        return $this->error_message;
    }

    protected function postToPaypal($method_name, $nvp_parameters, $permssions=false) {
        // Set the API operation, version, and API signature in the request.
        $request_parameters = array (
            'METHOD'    => $method_name,
            'VERSION'   => $this->api_version,
            'PWD'       => $this->api_password,
            'USER'      => $this->api_username,
            'SIGNATURE' => $this->api_signature
        );

        $headers = array();

        if ($permissions) {
            //TODO: get merchant email via API
            $this->merchant_email = "permissions@cashmusic.org";
            if ($this->merchant_email) {
                $request_parameters['SUBJECT'] = $this->merchant_email;
            }

            $headers = array(
                0 => "X-PAYPAL-SECURITY-SUBJECT: " . $this->merchant_email,
                1 => "X-PAYPAL-AUTHENTICATION: " . $this->getAuthorizationHeader()
            );
        }

        $request_parameters = array_merge($request_parameters, $nvp_parameters);

        // Get response from the server.
        $http_response = CASHSystem::getURLContents($this->api_endpoint,$request_parameters,true, $headers);
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


    protected static function getEmailFromPermissionsAPI($authorization) {

        $headers = array(
            "X-PP-AUTHORIZATION: " .$authorization,
            "X-PAYPAL-REQUEST-DATA-FORMAT:NV",
            "X-PAYPAL-RESPONSE-DATA-FORMAT:json",
            "X-PAYPAL-APPLICATION-ID: APP-1JE4291016473214C",
            );

        $url_api = "https://svcs.paypal.com/Permissions/GetBasicPersonalData";
        $post_array = array(
            "attributeList"=>array("attribute"=> array(0=>"http://axschema.org/contact/email")),
            "requestEnvelope.errorLanguage"=>"en_US");
        $curl_session =  curl_init();
        curl_setopt($curl_session, CURLOPT_URL,$url_api );
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl_session, CURLOPT_POST, 1);
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, http_build_query($post_array));
        curl_setopt($curl_session, CURLOPT_CONNECTTIMEOUT,10);
        curl_setopt($curl_session, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_session, CURLOPT_SSL_VERIFYPEER, 0);
        $response = json_decode(curl_exec($curl_session));

        error_log( print_r($response, true) );
        curl_close($curl_session);
    }

    public function preparePayment(
        $total_price,
        $order_sku,
        $order_name,
        $return_url,
        $cancel_url,
        $currency_id='USD', /* 'USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD' */
        $payment_type='Sale', /* 'Sale', 'Order', or 'Authorization' */
        $shipping=null
    ) {

        error_log($return_url."\n".$cancel_url);
        // Set NVP variables:
        $nvp_parameters = array(
            'PAYMENTREQUEST_0_AMT' => $total_price,
            'PAYMENTREQUEST_0_PAYMENTACTION' => $payment_type,
            'PAYMENTREQUEST_0_CURRENCYCODE' => $currency_id,
            'PAYMENTREQUEST_0_ALLOWEDPAYMENTMETHOD' => 'InstantPaymentOnly',
            'PAYMENTREQUEST_0_DESC' => $order_name,
            'RETURNURL' => $return_url,
            'CANCELURL' => $cancel_url,
            'L_PAYMENTREQUEST_0_AMT0' => $total_price,
            'L_PAYMENTREQUEST_0_NUMBER0' => $order_sku,
            'L_PAYMENTREQUEST_0_NAME0' => $order_name,
            'NOSHIPPING' => '1',
            'ALLOWNOTE' => '0',
            'SOLUTIONTYPE' => 'Sole',
            'LANDINGPAGE' => 'Billing'
        );

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

    public function doPayment($payment_type='Sale') {
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
