<?php

use PayPal\Auth\PPSignatureCredential;
use PayPal\Auth\PPSubjectAuthorization;
use PayPal\Auth\PPTokenAuthorization;
use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPRequest;
use PayPal\Handler\PPSignatureAuthHandler;

class PPSignatureAuthHandlerTest extends PHPUnit_Framework_TestCase {
	
	protected function setup() {
		
	}
	
	protected function tearDown() {
	
	}
	
	/**
	 * @test
	 */
	public function testHeadersAddedForNVP() {
		
		$req = new PPRequest(new StdClass(), 'NV');
		$options = array('config' => array('mode' => 'sandbox'), 'serviceName' => 'AdaptivePayments', 'apiMethod' => 'ConvertCurrency');
						
		$handler = new PPSignatureAuthHandler();
		
		// Test that no headers are added if no credential is passed
		$httpConfig = new PPHttpConfig();
		$handler->handle($httpConfig, $req, $options);		
		$this->assertEquals(0, count($httpConfig->getHeaders()));
		
		// Test that the 3 token headers are added for first party API calls
		$httpConfig = new PPHttpConfig();
		$cred = new PPSignatureCredential('user', 'pass', 'sign');		
		$req->setCredential($cred);
		
		$handler->handle($httpConfig, $req, $options);		
		$this->assertEquals(3, count($httpConfig->getHeaders()));

		// Test addition of 'subject' HTTP header for subject based third party auth
		$httpConfig = new PPHttpConfig();
		$cred = new PPSignatureCredential('user', 'pass', 'sign');
		$cred->setThirdPartyAuthorization(new PPSubjectAuthorization('email@paypal.com'));
		$req->setCredential($cred);
		
		$handler->handle($httpConfig, $req, $options);
		$this->assertEquals(4, count($httpConfig->getHeaders()));
		$this->assertArrayHasKey('X-PAYPAL-SECURITY-SUBJECT', $httpConfig->getHeaders());
	
		// Test that no auth related HTTP headers (username, password, sign?) are 
		// added for token based third party auth
		$httpConfig = new PPHttpConfig();
		$req->getCredential()->setThirdPartyAuthorization(new PPTokenAuthorization('token', 'tokenSecret'));
				
		$handler->handle($httpConfig, $req, $options);		
		$this->assertEquals(0, count($httpConfig->getHeaders()));
	
	}
	
	
	/**
	 * @test
	 */
	public function testHeadersAddedForSOAP() {
	
		$options = array('config' => array('mode' => 'sandbox'), 'serviceName' => 'AdaptivePayments', 'apiMethod' => 'ConvertCurrency');		
		$req = new PPRequest(new StdClass(), 'SOAP');
		
		$handler = new PPSignatureAuthHandler();
		
		// Test that no headers are added if no credential is passed
		$httpConfig = new PPHttpConfig();
		$handler->handle($httpConfig, $req, $options);
		$this->assertEquals('', $req->getBindingInfo('securityHeader'));
		
		
		// Test that the 3 token SOAP headers are added for first party API calls
		$req = new PPRequest(new StdClass(), 'SOAP');
		$req->setCredential(new PPSignatureCredential('user', 'pass', 'sign'));
		$handler->handle($httpConfig, $req, $options);
		
		$this->assertContains('<ebl:Username>', $req->getBindingInfo('securityHeader'));
		$this->assertContains('<ebl:Password>', $req->getBindingInfo('securityHeader'));
		$this->assertContains('<ebl:Signature>', $req->getBindingInfo('securityHeader'));
	
		// Test addition of 'subject' SOAP header for subject based third party auth
		$req = new PPRequest(new StdClass(), 'SOAP');
		$cred = new PPSignatureCredential('user', 'pass', 'sign');
		$cred->setThirdPartyAuthorization(new PPSubjectAuthorization('email@paypal.com'));
		$req->setCredential($cred);
		$handler->handle($httpConfig, $req, $options);
		
		$this->assertContains('<ebl:Username>', $req->getBindingInfo('securityHeader'));
		$this->assertContains('<ebl:Password>', $req->getBindingInfo('securityHeader'));
		$this->assertContains('<ebl:Signature>', $req->getBindingInfo('securityHeader'));
		$this->assertContains('<ebl:Subject>', $req->getBindingInfo('securityHeader'));
		
	
		// Test that no auth related HTTP headers (username, password, sign?) are
		// added for token based third party auth
		$req->getCredential()->setThirdPartyAuthorization(new PPTokenAuthorization('token', 'tokenSecret'));		
		$handler->handle($httpConfig, $req, $options);

		$this->assertContains('<ns:RequesterCredentials/>', $req->getBindingInfo('securityHeader'));
		$this->assertEquals(0, count($httpConfig->getHeaders()));
	
	}
	
	
}