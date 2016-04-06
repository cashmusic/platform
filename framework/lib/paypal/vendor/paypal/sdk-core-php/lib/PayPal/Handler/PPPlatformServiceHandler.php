<?php
namespace PayPal\Handler;

use PayPal\Core\PPConstants;
use PayPal\Core\PPCredentialManager;
use PayPal\Handler\PPAuthenticationHandler;
use PayPal\Handler\PPGenericServiceHandler;
use PayPal\Exception\PPConfigurationException;


/**
 *
 * Adds non-authentication headers that are specific to
 * PayPal's platform APIs and determines endpoint to
 * hit based on configuration parameters.
 *
 */
class PPPlatformServiceHandler extends PPGenericServiceHandler {

	private $apiUsername;
	
	public function __construct($apiUsername, $sdkName, $sdkVersion) {
		parent::__construct($sdkName, $sdkVersion);
		$this->apiUsername = $apiUsername;
	}

	public function handle($httpConfig, $request, $options) {

		parent::handle($httpConfig, $request, $options);
			
		if(is_string($this->apiUsername) || is_null($this->apiUsername)) {			
			// $apiUsername is optional, if null the default account in config file is taken
			$credMgr = PPCredentialManager::getInstance($options['config']);
			$request->setCredential(clone($credMgr->getCredentialObject($this->apiUsername)));
		} else {
			$request->setCredential($this->apiUsername);
		}
	

		$config = $options['config'];
		$credential = $request->getCredential();
		//TODO: Assuming existence of getApplicationId
		if($credential && $credential->getApplicationId() != NULL) {
			$httpConfig->addHeader('X-PAYPAL-APPLICATION-ID', $credential->getApplicationId());
		}
		if(isset($config['port']) && isset($config['service.EndPoint.'.$options['port']]))
		{
			$endpoint = $config['service.EndPoint.'.$options['port']];
		}
		// for backward compatibilty (for those who are using old config files with 'service.EndPoint')
		else if (isset($config['service.EndPoint']))
		{
			$endpoint = $config['service.EndPoint'];
		}
		else if (isset($config['mode']))
		{
			if(strtoupper($config['mode']) == 'SANDBOX')
			{
				$endpoint = PPConstants::PLATFORM_SANDBOX_ENDPOINT;
			}
			else if(strtoupper($config['mode']) == 'LIVE')
			{
				$endpoint = PPConstants::PLATFORM_LIVE_ENDPOINT;
			}
            else if(strtoupper($config['mode']) == 'TLS')
            {
                $endpoint = PPConstants::PLATFORM_TLS_ENDPOINT;
            }
		}
		else
		{
			throw new PPConfigurationException('endpoint Not Set');
		}
		$httpConfig->setUrl($endpoint . $options['serviceName'] . '/' .  $options['apiMethod']);
	
		// Call the authentication handler to tack authentication related info
		$handler = new PPAuthenticationHandler();
		$handler->handle($httpConfig, $request, $options);
	}
}
