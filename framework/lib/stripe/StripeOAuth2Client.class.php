<?php
require_once(dirname(__FILE__) . '/'.'../oauth2/OAuth2Client.php');
    // dependecy checks
    if (!class_exists('OAuth2Client')) {
        throw new Exception(
            '*OAuth2Client* is required. Please include it ' .
            '(https://github.com/quizlet/oauth2-php) before using this library.'
        );
    }

    /**
     * StripeOAuth2Client
     * 
     * Extends the <OAuth2Client> class to include a reference to the body of
     * the last request that was made.
     * 
     * Useful for Stripe, as it includes relevant information (eg. publisher
     * key, user id, etc.) within the access-token request.
     * 
     * Not sure if that's standard-practice, but this helps get around that
     * caveat.
     * 
     * Also, this is required anyway, since <quizlet>'s library is defined as
     * abstract (https://github.com/quizlet/oauth2-php).
     * 
     * @see      <https://github.com/quizlet/oauth2-php>
     * @see      <https://code.google.com/p/oauth/>
     * @thanks   Eric Ma <eric@skillshare.com>
     * @author   Oliver Nassar <onassar@gmail.com>
     * @extends  OAuth2Client
     */
    class StripeOAuth2Client extends OAuth2Client
    {
        /**
         * _last
         * 
         * The last response make, in raw form (eg. not json encoded, or
         * anything).
         * 
         * @var    string
         * @access private
         */
        private $_last;

        /**
         * makeRequest
         * 
         * See parent for full document of parameters. Extends the parent to
         * store the last-made request locally.
         * 
         * @access public
         * @param  string $path
         * @param  string $method (default: 'GET')
         * @param  array $params (default: Array)
         * @param  mixed $ch (default: NULL)
         * @return string
         */
        protected function makeRequest(
            $path,
            $method = 'GET',
            $params = array(),
            $ch = NULL
        ) {
           $args = func_get_args();
           $body = call_user_func_array(array('parent', 'makeRequest'), $args);
           $this->_last = $body;
           return $body;
        }

        /**
         * getLastResponse
         * 
         * Getter for the last response made.
         * 
         * @access public
         * @return string
         */
        public function getLastResponse()
        {
           return $this->_last;
        }
    }
