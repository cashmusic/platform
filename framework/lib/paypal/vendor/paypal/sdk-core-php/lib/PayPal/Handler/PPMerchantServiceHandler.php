<?php
namespace PayPal\Handler;

use PayPal\Exception\PPConfigurationException;
use PayPal\Core\PPCredentialManager;
use PayPal\Auth\PPSignatureCredential;
use PayPal\Auth\PPCertificateCredential;
use PayPal\Core\PPConstants;
use PayPal\Handler\PPAuthenticationHandler;

/**
 *
 * Adds non-authentication headers that are specific to
 * PayPal's Merchant APIs and determines endpoint to
 * hit based on configuration parameters.
 *
 */
class PPMerchantServiceHandler extends PPGenericServiceHandler {
	
	private $apiUsername;	

	public function __construct($apiUsername, $sdkName, $sdkVersion) {
		parent::__construct($sdkName, $sdkVersion);
		$this->apiUsername = $apiUsername;
	}

	public function handle($httpConfig, $request, $options) {
		parent::handle($httpConfig, $request, $options);
		$config = $options['config'];

		if(is_string($this->apiUsername) || is_null($this->apiUsername)) {			
			// $apiUsername is optional, if null the default account in config file is taken
			$credMgr = PPCredentialManager::getInstance($options['config']);
			$request->setCredential(clone($credMgr->getCredentialObject($this->apiUsername)));
		} else {
			$request->setCredential($this->apiUsername);
		}

		$endpoint = '';
		$credential = $request->getCredential();
		if(isset($options['port']) && isset($config['service.EndPoint.'.$options['port']]))
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
				if($credential instanceof PPSignatureCredential)
				{
					$endpoint = PPConstants::MERCHANT_SANDBOX_SIGNATURE_ENDPOINT;
				}
				else if($credential instanceof PPCertificateCredential)
				{
					$endpoint = PPConstants::MERCHANT_SANDBOX_CERT_ENDPOINT;
				}
			}
			else if(strtoupper($config['mode']) == 'LIVE')
			{
			if($credential instanceof PPSignatureCredential)
				{
					$endpoint = PPConstants::MERCHANT_LIVE_SIGNATURE_ENDPOINT;
				}
				else if($credential instanceof PPCertificateCredential)
				{
					$endpoint = PPConstants::MERCHANT_LIVE_CERT_ENDPOINT;
				}
			}else if(strtoupper($config['mode']) == 'TLS')
            {
                if($credential instanceof PPSignatureCredential)
                {
                    $endpoint = PPConstants::MERCHANT_TLS_SIGNATURE_ENDPOINT;
                }
                else if($credential instanceof PPCertificateCredential)
                {
                    $endpoint = PPConstants::MERCHANT_TLS_CERT_ENDPOINT;
                }
            }
		}
		else
		{
			throw new PPConfigurationException('endpoint Not Set');
		}
		
		if($request->getBindingType() == 'SOAP')
		{
			$httpConfig->setUrl($endpoint);
		}
		else 
		{
			throw new PPConfigurationException('expecting service binding to be SOAP');
		}
		
		$request->addBindingInfo("namespace", "xmlns:ns=\"urn:ebay:api:PayPalAPI\" xmlns:ebl=\"urn:ebay:apis:eBLBaseComponents\" xmlns:cc=\"urn:ebay:apis:CoreComponentTypes\" xmlns:ed=\"urn:ebay:apis:EnhancedDataTypes\"");
		// Call the authentication handler to tack authentication related info
		$handler = new PPAuthenticationHandler();
		$handler->handle($httpConfig, $request, $options);
	}
}