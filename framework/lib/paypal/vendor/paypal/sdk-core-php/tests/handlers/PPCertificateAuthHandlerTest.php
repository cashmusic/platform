<?php
use PayPal\Auth\PPCertificateCredential;
use PayPal\Auth\PPSubjectAuthorization;
use PayPal\Auth\PPTokenAuthorization;
use PayPal\Handler\PPCertificateAuthHandler;
use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPRequest;

class PPCertificateAuthHandlerTest extends PHPUnit_Framework_TestCase {
	
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
						
		$handler = new PPCertificateAuthHandler();
		
		// Test that no headers are added if no credential is passed
		$httpConfig = new PPHttpConfig();
		$handler->handle($httpConfig, $req, $options);		
		$this->assertEquals(0, count($httpConfig->getHeaders()));
		
		// Test that the 3 token headers are added for first party API calls
		$httpConfig = new PPHttpConfig();
		$cred = new PPCertificateCredential('user', 'pass', 'cacert.pem');		
		$req->setCredential($cred);
		
		$handler->handle($httpConfig, $req, $options);		
		$this->assertEquals(2, count($httpConfig->getHeaders()));
		$this->assertArrayHasKey(CURLOPT_SSLCERT, $httpConfig->getCurlOptions());
		
		// Test addition of 'subject' HTTP header for subject based third party auth
		$httpConfig = new PPHttpConfig();
		$cred = new PPCertificateCredential('user', 'pass', 'cacert.pem');
		$cred->setThirdPartyAuthorization(new PPSubjectAuthorization('email@paypal.com'));
		$req->setCredential($cred);
		
		$handler->handle($httpConfig, $req, $options);
		$this->assertEquals(3, count($httpConfig->getHeaders()));
		$this->assertArrayHasKey('X-PAYPAL-SECURITY-SUBJECT', $httpConfig->getHeaders());
		$this->assertArrayHasKey(CURLOPT_SSLCERT, $httpConfig->getCurlOptions());
		
		// Test that no auth related HTTP headers (username, password, sign?) are 
		// added for token based third party auth
		$httpConfig = new PPHttpConfig();
		$req->getCredential()->setThirdPartyAuthorization(new PPTokenAuthorization('token', 'tokenSecret'));
				
		$handler->handle($httpConfig, $req, $options);		
		$this->assertEquals(0, count($httpConfig->getHeaders()));
		$this->assertArrayHasKey(CURLOPT_SSLCERT, $httpConfig->getCurlOptions());
	
	}
	
	
	/**
	 * @test
	 */
	public function testHeadersAddedForSOAP() {
	
		$options = array('config' => array('mode' => 'sandbox'), 'serviceName' => 'AdaptivePayments', 'apiMethod' => 'ConvertCurrency');		
		$req = new PPRequest(new StdClass(), 'SOAP');
		
		$handler = new PPCertificateAuthHandler();
		
		// Test that no headers are added if no credential is passed
		$httpConfig = new PPHttpConfig();
		$handler->handle($httpConfig, $req, $options);
		$this->assertEquals('', $req->getBindingInfo('securityHeader'));		
		
		// Test that the 3 token SOAP headers are added for first party API calls
		$req = new PPRequest(new StdClass(), 'SOAP');
		$req->setCredential(new PPCertificateCredential('user', 'pass', 'cacert.pem'));
		$handler->handle($httpConfig, $req, $options);
		
		$this->assertContains('<ebl:Username>', $req->getBindingInfo('securityHeader'));
		$this->assertContains('<ebl:Password>', $req->getBindingInfo('securityHeader'));		
		$this->assertArrayHasKey(CURLOPT_SSLCERT, $httpConfig->getCurlOptions());
		
		// Test addition of 'subject' SOAP header for subject based third party auth
		$req = new PPRequest(new StdClass(), 'SOAP');
		$cred = new PPCertificateCredential('user', 'pass', 'cacert.pem');
		$cred->setThirdPartyAuthorization(new PPSubjectAuthorization('email@paypal.com'));
		$req->setCredential($cred);
		$handler->handle($httpConfig, $req, $options);
		
		$this->assertContains('<ebl:Username>', $req->getBindingInfo('securityHeader'));
		$this->assertContains('<ebl:Password>', $req->getBindingInfo('securityHeader'));		
		$this->assertContains('<ebl:Subject>', $req->getBindingInfo('securityHeader'));
		$this->assertArrayHasKey(CURLOPT_SSLCERT, $httpConfig->getCurlOptions());
		
	
		// Test that no auth related HTTP headers (username, password, sign?) are
		// added for token based third party auth
		$req = new PPRequest(new StdClass(), 'SOAP');
		$req->setCredential(new PPCertificateCredential('user', 'pass', 'cacert.pem'));
		$req->getCredential()->setThirdPartyAuthorization(new PPTokenAuthorization('token', 'tokenSecret'));		
		$handler->handle($httpConfig, $req, $options);

		$this->assertContains('<ns:RequesterCredentials/>', $req->getBindingInfo('securityHeader'));
		$this->assertEquals(0, count($httpConfig->getHeaders()));
		$this->assertArrayHasKey(CURLOPT_SSLCERT, $httpConfig->getCurlOptions());
	}
	
}
