<?php
use PayPal\Common\PPApiContext;
use PayPal\Core\PPConstants;
use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPRequest;
use PayPal\Handler\PPOpenIdHandler;

class PPOpenIdHandlerTest extends PHPUnit_Framework_TestCase {
	
	protected function setup() {
		
	}
	
	protected function tearDown() {
	
	}
	
	/**
	 * @test
	 */
	public function testInvalidConfiguration() {
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('mode' => 'unknown', 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler();
	
		$this->setExpectedException('PayPal\Exception\PPConfigurationException');
		$handler->handle($httpConfig, 'payload', array('path' => '/path', 'apiContext' => $apiContext));
		
		
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler($apiContext);
		
		$this->setExpectedException('PayPal\Exception\PPConfigurationException');
		$handler->handle($httpConfig, 'payload', array('path' => '/path', 'apiContext' => $apiContext));
	}
	
	/**
	 * @test
	 */
	public function testHeadersAdded() {
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('mode' => 'sandbox', 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		
		$handler = new PPOpenIdHandler();
		$handler->handle($httpConfig, 'payload', array('apiContext' => $apiContext));
		
		$this->assertArrayHasKey('Authorization', $httpConfig->getHeaders());
		$this->assertArrayHasKey('User-Agent', $httpConfig->getHeaders());			
		$this->assertContains('PayPalSDK', $httpConfig->getHeader('User-Agent'));
	}
	
	/**
	 * @test
	 */
	public function testModeBasedEndpoint() {
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('mode' => 'sandbox', 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));		
		$handler = new PPOpenIdHandler();
		
		$handler->handle($httpConfig, 'payload', array('path' => '/path', 'apiContext' => $apiContext));
		$this->assertEquals(PPConstants::REST_SANDBOX_ENDPOINT . "path", $httpConfig->getUrl());
		
		
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('mode' => 'live', 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler();
		
		$handler->handle($httpConfig, 'payload', array('path' => '/path', 'apiContext' => $apiContext));
		$this->assertEquals(PPConstants::REST_LIVE_ENDPOINT . "path", $httpConfig->getUrl());
	}
	
	/**
	 * @test
	 */
	public function testCustomEndpoint() {
		$customEndpoint = 'http://mydomain';
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('service.EndPoint' => $customEndpoint, 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler();
		
		$handler->handle($httpConfig, 'payload', array('path' => '/path', 'apiContext' => $apiContext));
		$this->assertEquals("$customEndpoint/path", $httpConfig->getUrl());
		
		
		$customEndpoint = 'http://mydomain/';
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('service.EndPoint' => $customEndpoint, 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler();
		
		$handler->handle($httpConfig, 'payload', array('path' => '/path', 'apiContext' => $apiContext));
		$this->assertEquals("${customEndpoint}path", $httpConfig->getUrl());
		
		
		$customEndpoint = 'http://mydomain';
		$httpConfig = new PPHttpConfig();
		$apiContext = new PPApiContext(array('service.EndPoint' => 'xyz', 'openid.EndPoint' => $customEndpoint, 'acct1.ClientId' => 'clientId', 'acct1.ClientSecret' => 'clientSecret'));
		$handler = new PPOpenIdHandler();
		
		$handler->handle($httpConfig, 'payload', array('path' => '/path', 'apiContext' => $apiContext));
		$this->assertEquals("$customEndpoint/path", $httpConfig->getUrl());
	}
	
}
