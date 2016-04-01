<?php
use PayPal\Auth\PPSignatureCredential;
use PayPal\Auth\PPTokenAuthorization;
use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPRequest;
use PayPal\Handler\PPAuthenticationHandler;

class PPAuthenticationHandlerTest extends PHPUnit_Framework_TestCase {
	
	protected function setup() {
		
	}
	
	protected function tearDown() {
	
	}
	
	/**
	 * @test
	 */
	public function testValidConfiguration() {
		
		$credential = new PPSignatureCredential('user', 'pass', 'sign');
		$credential->setThirdPartyAuthorization(new PPTokenAuthorization('accessToken', 'tokenSecret'));
		$options = array('config' => array('mode' => 'sandbox'), 'serviceName' => 'DoExpressCheckout', 'port' => 'PayPalAPI');
		
		$req = new PPRequest(new StdClass(), 'SOAP');
		$req->setCredential($credential);
		
		$httpConfig = new PPHttpConfig('http://api.paypal.com');
		
		$handler = new PPAuthenticationHandler();
		$handler->handle($httpConfig, $req, $options);		
		$this->assertArrayHasKey('X-PP-AUTHORIZATION', $httpConfig->getHeaders());
		
		$options['port'] = 'abc';
		$handler->handle($httpConfig, $req, $options);
		$this->assertArrayHasKey('X-PAYPAL-AUTHORIZATION', $httpConfig->getHeaders());
		
		unset($options['port']);
		$handler->handle($httpConfig, $req, $options);
		$this->assertArrayHasKey('X-PAYPAL-AUTHORIZATION', $httpConfig->getHeaders());
	}
}