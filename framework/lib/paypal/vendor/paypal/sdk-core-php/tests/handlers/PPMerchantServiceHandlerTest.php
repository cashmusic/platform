<?php
use PayPal\Auth\PPSignatureCredential;
use PayPal\Auth\PPCertificateCredential;
use PayPal\Auth\PPTokenAuthorization;
use PayPal\Handler\PPMerchantServiceHandler;
use PayPal\Core\PPConstants;
use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPRequest;

class PPMerchantServiceHandlerTest extends PHPUnit_Framework_TestCase {
	
	private $options;
	
	protected function setup() {
		$this->options = array(
			'config' => array(
				'mode' => 'sandbox', 
				'acct1.UserName' => 'siguser', 
				'acct1.Password' => 'pass', 
				'acct1.Signature' => 'signature', 
				'acct2.UserName' => 'certuser', 
				'acct2.Password' => 'pass', 
				'acct2.CertPath' => 'pathtocert', 
			), 
			'serviceName' => 'PayPalAPIInterfaceService', 
			'apiMethod' => 'DoExpressCheckout',
			'port' => 'apiAA'
		);
	}
	
	protected function tearDown() {
	
	}
	
	/**
	 * @test
	 */
	public function testHeadersAdded() {
		
		$req = new PPRequest(new StdClass(), 'SOAP');
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPMerchantServiceHandler(null, 'sdkname', 'sdkversion');
		$handler->handle($httpConfig, $req, $this->options);
		
		$this->assertEquals(4, count($httpConfig->getHeaders()), "Basic headers not added");
		
	}
	
	/**
	 * @test
	 */
	public function testModeBasedEndpointForSignatureCredential() {
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPMerchantServiceHandler(null, 'sdkname', 'sdkversion');
		$req = new PPRequest(new StdClass(), 'SOAP');
		$req->setCredential(new PPSignatureCredential('a', 'b', 'c'));
		
		$handler->handle($httpConfig, $req, $this->options);
		$this->assertEquals(PPConstants::MERCHANT_SANDBOX_SIGNATURE_ENDPOINT, $httpConfig->getUrl());
		
		$options = $this->options;
		$options['config']['mode'] = 'live';
		$handler->handle($httpConfig, $req, $options);
		$this->assertEquals(PPConstants::MERCHANT_LIVE_SIGNATURE_ENDPOINT, $httpConfig->getUrl());
		
	}
	
	
	/**
	 * @test
	 */
	public function testModeBasedEndpointForCertificateCredential() {
	
		$httpConfig = new PPHttpConfig();
		$handler = new PPMerchantServiceHandler('certuser', 'sdkname', 'sdkversion');
		$req = new PPRequest(new StdClass(), 'SOAP');
	
		$handler->handle($httpConfig, $req, $this->options);
		$this->assertEquals(PPConstants::MERCHANT_SANDBOX_CERT_ENDPOINT, $httpConfig->getUrl());
	
		$options = $this->options;
		$options['config']['mode'] = 'live';
		$handler->handle($httpConfig, $req, $options);
		$this->assertEquals(PPConstants::MERCHANT_LIVE_CERT_ENDPOINT, $httpConfig->getUrl());
	
	}
	
	
	public function testCustomEndpoint() {

		$customEndpoint = 'http://myhost/';
		$options = $this->options;
		$options['config']['service.EndPoint'] = $customEndpoint;
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPMerchantServiceHandler(null, 'sdkname', 'sdkversion');
		
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'SOAP'), $options			
		);
		$this->assertEquals("$customEndpoint", $httpConfig->getUrl(), "Custom endpoint not processed");
	
		$options['config']['service.EndPoint'] = 'abc';
		$options['config']["service.EndPoint.". $options['port']] = $customEndpoint;
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'SOAP'), $options
		);
		$this->assertEquals("$customEndpoint", $httpConfig->getUrl(), "Custom endpoint not processed");
	
	}
	
	/**
	 * @test
	 */
	 public function testInvalidConfigurations() {
		$httpConfig = new PPHttpConfig();
		$handler = new PPMerchantServiceHandler(null, 'sdkname', 'sdkversion');
		
		$this->setExpectedException('PayPal\Exception\PPMissingCredentialException');
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'SOAP'),
				array('config' => array())
		);
		$this->setExpectedException('PayPal\Exception\PPConfigurationException');
		
		
		$options = $this->options;
		unset($options['mode']);
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'SOAP'),
				$options
		);
	 }

	/**
	 * @test
	 */
	 public function testSourceHeader() {
		$httpConfig = new PPHttpConfig();
		$handler = new PPMerchantServiceHandler(null, 'sdkname', 'sdkversion');
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'SOAP'),
				$this->options
		);

		$headers = $httpConfig->getHeaders();
		$this->assertArrayHasKey('X-PAYPAL-REQUEST-SOURCE', $headers);
		$this->assertRegExp('/.*sdkname.*/', $headers['X-PAYPAL-REQUEST-SOURCE']);
		$this->assertRegExp('/.*sdkversion.*/', $headers['X-PAYPAL-REQUEST-SOURCE']);
	}
}
