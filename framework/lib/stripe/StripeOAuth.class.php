<?php

    // include the Stripe version of the OAuth2 Client
    require_once('StripeOAuth2Client.class.php');

    /**
     * StripeOAuth
     * 
     * Library which helps perform an OAuth2 flow for Stripe, which is used when
     * creating a Stripe application.
     * 
     * Stripe applications allow merchants (with their own Stripe account) to
     * give you permission to receive payments on their behalf.
     * 
     * @see      <https://github.com/quizlet/oauth2-php>
     * @see      <https://stripe.com/docs/apps/reference>
     * @see      <https://code.google.com/p/oauth/>
     * @thanks   Eric Ma <eric@skillshare.com>
     * @author   Oliver Nassar <onassar@gmail.com>
     * @example
     * <code>
     *     // Redirect Logic
     *     $oauth = (new StripeOAuth(
     *         'ca_********************************',
     *         'sk_*****************************'
     *     ));
     *     $url = $oauth->getAuthorizeUri();
     *     header('Location: ' . ($url));
     *     exit(0);
     *  
     *     // Callback: Access token and Publisher Key
     *     $oauth = (new StripeOAuth(
     *         'ca_********************************',
     *         'sk_*****************************'
     *     ));
     *     $token = $oauth->getAccessToken($_GET['code']);
     *     $key = $oauth->getPublishableKey($_GET['code']);
     *     echo 'Access token: ' . ($token) . '<br />Key: ' . ($key);
     *     exit(0);
     * <code>
     */
    class StripeOAuth
    {
        /**
         * _body
         * 
         * The response body, in json form, of the access-token request. OAuth2
         * doesn't seem to generally provide access to this, so it's extended
         * through the <StripeOAuth2Client> class to provide that helper.
         * 
         * @var    string
         * @access private
         */
        private $_body;

        /**
         * _client
         * 
         * A reference to the <OAuth2Client> instance that Stripe is
         * authenticating through.
         * 
         * @var    OAuth2Client
         * @access private
         */
        private $_client;

        /**
         * _cid
         * 
         * The Client ID of the Stripe application that is initiating the OAuth2
         * connection.
         * 
         * @var    string
         * @access private
         */
        private $_cid;

        /**
         * _endpoints
         * 
         * The URI endpoints for the Stripe connection flow.
         * 
         * @var    array
         * @access private
         */
        private $_endpoints = array(
            'base' => 'https://connect.stripe.com',
            'access' => '/oauth/authorize',
            'token' => '/oauth/token'
        );

        /**
         * _secret
         * 
         * The secret key of the account that the application was registered
         * under.
         * 
         * @var    string
         * @access private
         */
        private $_secret;

        /**
         * _tokens
         * 
         * An array of tokens (access and refresh) that were retrieved in the
         * connection flow.
         * 
         * @var    array (default: array());
         * @access private
         */
        private $_tokens = array();

        /**
         * __construct
         * 
         * Sets the Client ID of the application and the secret of the account
         * that the application is registered under.
         * 
         * @access public
         * @param  String $cid The client ID (testing or live) of the
         *         application. Can be found in the *Applications* section of
         *         your Stripe account's *Account Settings*
         * @param  String $secret The secret (either testing or live) for the
         *         *account* that the application was created through
         * @return void
         */
        public function __construct($cid, $secret)
        {
            $this->_cid = $cid;
            $this->_secret = $secret;
        }

        /**
         * _getClient
         * 
         * Creates and returns the <OAuth2Client> resource that processes the
         * connection flow. If it's already been created, a reference to it is
         * returned (rather than re-created).
         * 
         * @access private
         * @return OAuth2Client
         */
        private function _getClient()
        {
            if (!isset($this->_client)) {
                $this->_client = (new StripeOAuth2Client(array(
                    'base_uri' => $this->_endpoints['base'],
                    'client_id' => $this->_cid,
                    'client_secret' => $this->_secret,
                    'access_token_uri' => $this->_endpoints['token']
                )));
            }
            return $this->_client;
        }

        /**
         * getAccessToken
         * 
         * Generates and returns the access token for the connection flow. Also
         * allows for the access token generation to be based off of a refresh
         * token if <refresh> is set to <true>.
         * 
         * By default, access tokens are retrieved using an authorization code,
         * rather than a refresh token.
         * 
         * @access public
         * @param  String $code
         * @param  Boolean $refresh (default: false)
         * @return String
         */
        public function getAccessToken($code, $refresh = false)
        {
            $tokens = $this->getTokens($code, $refresh);
            return $tokens['access'];
        }

        /**
         * getAuthorizeUri
         * 
         * Returns the endpoint-URI that a user should be redirected to in order
         * to authorize an application to act on their behalf.
         * 
         * This is just part of the flow, however, as after they have authorized
         * the application, an access token still needs to be generated and
         * retrieved on their behalf.
         * 
         * @todo   include <state> parameter for CSRF consideration
         * @todo   add ability to specify default values, as listed below
         * @access public
         * @param  Array  $params (default: array())
         * @param  String $scope (default: read_write)
         * @return String
         */
        public function getAuthorizeUri(
            $params = array(),
            $scope = 'read_write'
        ) {
            /*
                Default values:
                    stripe_user[email]: the user's email address
                    stripe_user[url]: the URL where Stripe will be used, often on your own website
                    stripe_user[phone_number]: the business phone number
                    stripe_user[business_name]: the legal name of the business
                    stripe_user[first_name]: first name of the person applying
                    stripe_user[last_name]: last name of the person applying
                    stripe_user[street_address]: street address of the person applying
                    stripe_user[city]: city of the person applying
                    stripe_user[state]: two digit state code as a string
                    stripe_user[zip]: five digit zip code as a string
                    stripe_user[physical_product]: true if the user sells a physical product, false otherwise
            */

            // default params to send a long
            $defaults = array(
                'response_type' => 'code',
                'client_id' => ($this->_cid),
                'scope' => $scope
            );
            // merge with passed in
            $params = array_merge($defaults, $params);

            // build the url
            $base = $this->_endpoints['base'];
            $access = $this->_endpoints['access'];
            $query = http_build_query($params);
            return ($base) . ($access) . '?' . ($query);
        }

        /**
         * getBody
         * 
         * @access public
         * @return Array
         */
        public function getBody()
        {
            if (empty($this->_tokens)) {
                throw new Exception(
                    'Access token hasn\'t been generated yet.'
                );
            }
            return $this->_body;
        }

        /**
         * getPublishableKey
         * 
         * @access public
         * @return String
         */
        public function getPublishableKey()
        {
            if (empty($this->_tokens)) {
                throw new Exception(
                    'Access token hasn\'t been generated yet.'
                );
            }
            return $this->_body->stripe_publishable_key;
        }

        /**
         * getRefreshToken
         * 
         * Generates and returns the refresh token for the connection flow. Also
         * allows for the refresh token generation to be based off of a refresh
         * token itself, if <refresh> is set to <true>.
         * 
         * By default, refresh tokens are retrieved using an authorization code,
         * rather than a refresh token.
         * 
         * @access public
         * @param  String $code
         * @param  Boolean $refresh (default: false)
         * @return String
         */
        public function getRefreshToken($code, $refresh = true)
        {
            $tokens = $this->getTokens($code, $refresh);
            return $tokens['refresh'];
        }

        /**
         * getScope
         * 
         * @access public
         * @return String
         */
        public function getScope()
        {
            if (empty($this->_tokens)) {
                throw new Exception(
                    'Access token hasn\'t been generated yet.'
                );
            }
            return $this->_body->scope;
        }

        /**
         * getTokens
         * 
         * Retrieves and stores the access and refresh tokens for the OAuth
         * connection, depending on whether it's an authorization code being
         * passed in, or a refresh token.
         * 
         * This is distinguished via the the <refresh> boolean set to true.
         * 
         * @access public
         * @param  String $code
         * @param  Boolean $refresh (default: false)
         * @return Array
         */
        public function getTokens($code, $refresh = false)
        {
            // if tokens haven't been stored yet
            if (empty($this->_tokens)) {

                // retrieve OAuth2 resource
                $client = $this->_getClient();

                /**
                 * Set the grant type and code depending on whether the code
                 * being supplied is an authorization token or a refresh token
                 */
                if ($refresh === true) {
                    $client->setVariable('grant_type', 'refresh_token');
                    $client->setVariable('refresh_token', $code);
                } else {
                    $client->setVariable('grant_type', 'authorization_code');
                    $client->setVariable('code', $code);
                }

                // sign the request by setting a custom header
                $header = 'Authorization: Bearer ' . ($this->_secret);
                array_push($client::$CURL_OPTS[CURLOPT_HTTPHEADER], $header);

                // get the session (initiates the request)
                $session = $client->getSession();
                $this->_body = json_decode(
                    $this->_getClient()->getLastResponse()
                );

                // if it bailed
                if (isset($this->_body->code)) {
                    $body = $this->_body->message;
                    throw new Exception(
                        'Error making StripeOAuth request: (' .
                        ($this->_body->code) . ') ' . ($body)
                    );
                }

                // store the tokens
                $this->_tokens = array(
                    'access' => $session['access_token'],
                    'refresh' => $session['refresh_token']
                );
            }
            return $this->_tokens;
        }

        /**
         * getTokenType
         * 
         * @access public
         * @return String
         */
        public function getTokenType()
        {
            if (empty($this->_tokens)) {
                throw new Exception(
                    'Access token hasn\'t been generated yet.'
                );
            }
            return $this->_body->token_type;
        }

        /**
         * getUserId
         * 
         * @access public
         * @return String
         */
        public function getUserId()
        {
            if (empty($this->_tokens)) {
                throw new Exception(
                    'Access token hasn\'t been generated yet.'
                );
            }
            return $this->_body->stripe_user_id;
        }

        /**
         * isLive
         * 
         * @access public
         * @return Boolean
         */
        public function isLive()
        {
            if (empty($this->_tokens)) {
                throw new Exception(
                    'Access token hasn\'t been generated yet.'
                );
            }
            return $this->_body->livemode === true;
        }
    }
