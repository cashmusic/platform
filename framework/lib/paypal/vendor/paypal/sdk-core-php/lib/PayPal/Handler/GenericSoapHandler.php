<?php
namespace PayPal\Handler;
use PayPal\Auth\PPTokenAuthorization;
use PayPal\Auth\PPSubjectAuthorization;
use PayPal\Auth\Oauth\AuthSignature;
use PayPal\Core\PPConstants;
use PayPal\Handler\IPPHandler;

class GenericSoapHandler implements IPPHandler {

	private $namespace;
	
	public function __construct($namespace) {
		$this->namespace = $namespace;
	}
	
	public function handle($httpConfig, $request, $options) {
		
		if(isset($options['apiContext'])) {
			if($options['apiContext']->getHttpHeaders() != null) {
				$httpConfig->setHeaders($options['apiContext']->getHttpHeaders());
			}
			if($options['apiContext']->getSOAPHeader() != null) {
				$request->addBindingInfo('securityHeader', $options['apiContext']->getSOAPHeader()->toXMLString());
			}
		}
		
		if(isset($options['config']['service.EndPoint'])) {
			$httpConfig->setUrl($options['config']['service.EndPoint']);
		}
		if( !array_key_exists('Content-Type', $httpConfig->getHeaders())) {
			$httpConfig->addHeader('Content-Type', 'text/xml');
		}
		
		$request->addBindingInfo("namespace", $this->namespace);
		
	}	
}
