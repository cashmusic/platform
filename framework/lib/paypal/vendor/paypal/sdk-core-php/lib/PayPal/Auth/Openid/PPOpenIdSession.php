<?php
namespace PayPal\Auth\Openid;
use PayPal\Core\PPConstants;
use PayPal\Common\PPApiContext;

class PPOpenIdSession {

	/**
	 * Returns the PayPal URL to which the user must be redirected to
	 * start the authentication / authorization process.
	 *
	 * @param string $redirectUri Uri on merchant website to where
	 * 				the user must be redirected to post paypal login
	 * @param array $scope The access privilges that you are requesting for
	 * 				from the user. Pass empty array for all scopes.
	 * @param string $clientId client id from developer portal
	 * 				See https://developer.paypal.com/webapps/developer/docs/integration/direct/log-in-with-paypal/detailed/#attributes for more
	 * @param PPApiContext $apiContext Optional API Context
	 */
	public static function getAuthorizationUrl($redirectUri, $scope, $clientId, $nonce = null, $state = null, $apiContext=null) {

		if(is_null($apiContext)) {
			$apiContext = new PPApiContext();
		}

		$config = $apiContext->getConfig();

		if ($apiContext->get($clientId) !== false)
		    $clientId = $apiContext->get($clientId);

		$scope = count($scope) != 0 ? $scope : array('openid', 'profile', 'address', 'email', 'phone', 
					'https://uri.paypal.com/services/paypalattributes', 'https://uri.paypal.com/services/expresscheckout');
		if(!in_array('openid', $scope)) {
			$scope[] = 'openid';
		}
		
		$params = array(
				'client_id' => $clientId,
				'response_type' => 'code',
				'scope' => implode(" ", $scope),
				'redirect_uri' => $redirectUri
		);
		
		if ($nonce)
		    $params['nonce'] = $nonce;
		
		if ($state)
		    $params['state'] = $state;
		
		return sprintf("%s/v1/authorize?%s", self::getBaseUrl($config), http_build_query($params));
	}


	/**
	 * Returns the URL to which the user must be redirected to
	 * logout from the OpenID provider (i.e. PayPal)
	 *
	 * @param string $redirectUri Uri on merchant website to where
	 * 				the user must be redirected to post logout
	 * @param string $idToken id_token from the TokenInfo object
	 * @param PPApiContext $apiContext Optional API Context
	 */
	public static function getLogoutUrl($redirectUri, $idToken, $apiContext=null) {
		
		if(is_null($apiContext)) {
			$apiContext = new PPApiContext();
		}
		$config = $apiContext->getConfig();
		
		$params = array(
				'id_token' => $idToken,
				'redirect_uri' => $redirectUri,
				'logout' => 'true'
		);
		return sprintf("%s/v1/endsession?%s", self::getBaseUrl($config), http_build_query($params));
	}

	private static function getBaseUrl($config) {

		if(array_key_exists('openid.RedirectUri', $config)) {
			return $config['openid.RedirectUri'];
		} else if (array_key_exists('mode', $config)) {
			switch(strtoupper($config['mode'])) {
				case 'SANDBOX':
					return PPConstants::OPENID_REDIRECT_SANDBOX_URL;
				case 'LIVE':
					return PPConstants::OPENID_REDIRECT_LIVE_URL;
                case 'TLS':
                    return PPConstants::OPENID_REDIRECT_TLS_URL;
            }
		}
		return ;
	}
}
