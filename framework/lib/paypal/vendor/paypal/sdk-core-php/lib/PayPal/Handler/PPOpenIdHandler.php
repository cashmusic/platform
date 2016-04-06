<?php
namespace PayPal\Handler;

use PayPal\Exception\PPConfigurationException;
use PayPal\Common\PPUserAgent;
use PayPal\Core\PPConstants;

class PPOpenIdHandler implements IPPHandler {
	
	private static $sdkName = "openid-sdk-php";	
	private static $sdkVersion = "2.5.0";
	
	public function handle($httpConfig, $request, $options) {
		$apiContext = $options['apiContext'];
		$config = $apiContext->getConfig();
		$httpConfig->setUrl(
			rtrim(trim($this->_getEndpoint($config)), '/') . 
				(isset($options['path']) ? $options['path'] : '')
		);
		
		if(!array_key_exists("Authorization", $httpConfig->getHeaders())) {			
			$auth = base64_encode($config['acct1.ClientId'] . ':' . $config['acct1.ClientSecret']);
			$httpConfig->addHeader("Authorization", "Basic $auth");
		}
		if(!array_key_exists("User-Agent", $httpConfig->getHeaders())) {
			$httpConfig->addHeader("User-Agent", PPUserAgent::getValue(self::$sdkName, self::$sdkVersion));
		}
	}
	
	private function _getEndpoint($config) {
		if (isset($config['openid.EndPoint'])) {
			return $config['openid.EndPoint'];
		} else if (isset($config['service.EndPoint'])) {
			return $config['service.EndPoint'];
		} else if (isset($config['mode'])) {
			switch (strtoupper($config['mode'])) {
				case 'SANDBOX':
					return PPConstants::REST_SANDBOX_ENDPOINT;
				case 'LIVE':
					return PPConstants::REST_LIVE_ENDPOINT;
                case 'TLS':
                    return PPConstants::REST_TLS_ENDPOINT;
				default:
					throw new PPConfigurationException('The mode config parameter must be set to either sandbox/live/tls');
			}
		} else {
			throw new PPConfigurationException('You must set one of service.endpoint or mode parameters in your configuration');
		}
	}
}
