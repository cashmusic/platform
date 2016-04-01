<?php
use PayPal\Auth\PPSignatureCredential;
use PayPal\Core\PPConstants;
use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPRequest;
use PayPal\Handler\PPPlatformServiceHandler;

class PPPlatformServiceHandlerTest extends PHPUnit_Framework_TestCase {

	private $options;
	
	protected function setup() {
		$this->options = array(
			'config' => array(
				'mode' => 'sandbox', 
				'acct1.UserName' => 'user', 
				'acct1.Password' => 'pass', 
				'acct1.Signature' => 'sign', 
				'acct1.AppId' => 'APP',
				'acct2.UserName' => 'certuser', 
				'acct2.Password' => 'pass', 
				'acct2.CertPath' => 'pathtocert', 
			), 
			'serviceName' => 'AdaptivePayments', 
			'apiMethod' => 'ConvertCurrency');
	}
	
	protected function tearDown() {
	
	}
	
	/**
	 * @test
	 */
	public function testDefaultAPIAccount() {
		
		$req = new PPRequest(new StdClass(), 'NV');

		$httpConfig = new PPHttpConfig();
		$handler = new PPPlatformServiceHandler(null, 'sdkname', 'sdkversion');
		$handler->handle($httpConfig, $req, $this->options);
		$this->assertEquals($this->options['config']['acct1.Signature'], $req->getCredential()->getSignature());

		
		$cred = new PPSignatureCredential('user', 'pass', 'sig');
		$cred->setApplicationId('appId');
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPPlatformServiceHandler($cred, 'sdkname', 'sdkversion');
		$handler->handle($httpConfig, $req, $this->options);		
		
		$this->assertEquals($cred, $req->getCredential());
	}
	
	/**
	 * @test
	 */
	public function testHeadersAdded() {
		
		$req = new PPRequest(new StdClass(), 'NV');
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPPlatformServiceHandler(null, 'sdkname', 'sdkversion');
		$handler->handle($httpConfig, $req, $this->options);
		
		$this->assertEquals(8, count($httpConfig->getHeaders()), "Basic headers not added");
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPPlatformServiceHandler('certuser', 'sdkname', 'sdkversion');
		$handler->handle($httpConfig, $req, $this->options);
		
		$this->assertEquals(6, count($httpConfig->getHeaders()));
		$this->assertEquals('certuser', $req->getCredential()->getUsername());
	}
	
	/**
	 * @test
	 */
	public function testEndpoint() {
		$serviceName = 'AdaptivePayments';
		$apiMethod = 'ConvertCurrency';
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPPlatformServiceHandler(null, 'sdkname', 'sdkversion');
		
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NV'),
				$this->options
		);
		$this->assertEquals(PPConstants::PLATFORM_SANDBOX_ENDPOINT . "$serviceName/$apiMethod", $httpConfig->getUrl());

		$options = $this->options;
		$options['config']['mode'] = 'live';
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NV'),
				$options
		);
		$this->assertEquals(PPConstants::PLATFORM_LIVE_ENDPOINT . "$serviceName/$apiMethod", $httpConfig->getUrl());
		
		
		$customEndpoint = 'http://myhost/';
		$options = $this->options;
		$options['config']['service.EndPoint'] = $customEndpoint;
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NV'),
				$options
		);
		$this->assertEquals("$customEndpoint$serviceName/$apiMethod", $httpConfig->getUrl(), "Custom endpoint not processed");
		
	}

	/**
	 * @test
	 */
	 public function testInvalidConfigurations() {
		$httpConfig = new PPHttpConfig();
		$handler = new PPPlatformServiceHandler(null, 'sdkname', 'sdkversion');
		
		$this->setExpectedException('PayPal\Exception\PPMissingCredentialException');
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NV'),
				array('config' => array())
		);
		
		$this->setExpectedException('PayPal\Exception\PPConfigurationException');
		$options = $this->options;
		unset($options['mode']);
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NV'),
				$options
		);
	 }

	/**
	 * @test
	 */
	 public function testSourceHeader() {
		$httpConfig = new PPHttpConfig();
		$handler = new PPPlatformServiceHandler(null, 'sdkname', 'sdkversion');
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NV'),
				$this->options
		);

		$headers = $httpConfig->getHeaders();
		$this->assertArrayHasKey('X-PAYPAL-REQUEST-SOURCE', $headers);
		$this->assertRegExp('/.*sdkname.*/', $headers['X-PAYPAL-REQUEST-SOURCE']);
		$this->assertRegExp('/.*sdkversion.*/', $headers['X-PAYPAL-REQUEST-SOURCE']);
	 }
}
