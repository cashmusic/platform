<?php

use PayPal\Handler\PPGenericServiceHandler;
use PayPal\Core\PPHttpConfig;
use PayPal\Core\PPRequest;

class PPGenericServiceHandlerTest extends PHPUnit_Framework_TestCase {
	
	protected function setup() {
		
	}
	
	protected function tearDown() {
	
	}
	
	/**
	 * @test
	 */
	public function testHeadersAdded() {
		$bindingType = 'bindingType';
		$devEmail = 'developer@domain.com';
		
		
		$httpConfig = new PPHttpConfig();
		$handler = new PPGenericServiceHandler('sdkname', 'sdkversion');
		$handler->handle($httpConfig, 
				new PPRequest(new StdClass(), $bindingType), 
				array('config' => array('service.SandboxEmailAddress' => $devEmail))
		);
		
		$headers = $httpConfig->getHeaders();		
		$this->assertEquals(5, count($headers));
		$this->assertArrayHasKey('X-PAYPAL-DEVICE-IPADDRESS', $headers);
		$this->assertArrayHasKey('X-PAYPAL-REQUEST-SOURCE', $headers);		
		$this->assertEquals($bindingType, $headers['X-PAYPAL-REQUEST-DATA-FORMAT']);
		$this->assertEquals($bindingType, $headers['X-PAYPAL-RESPONSE-DATA-FORMAT']);
		$this->assertEquals($devEmail, $headers['X-PAYPAL-SANDBOX-EMAIL-ADDRESS']);
		
	}
	
	/**
	 * @test
	 */
	 public function testSourceHeader() {
		$httpConfig = new PPHttpConfig();
		$handler = new PPGenericServiceHandler('sdkname', 'sdkversion');
		$handler->handle($httpConfig,
				new PPRequest(new StdClass(), 'NV'),
				array('config' => array())
		);

		$headers = $httpConfig->getHeaders();
		$this->assertArrayHasKey('X-PAYPAL-REQUEST-SOURCE', $headers);
		$this->assertRegExp('/.*sdkname.*/', $headers['X-PAYPAL-REQUEST-SOURCE']);
		$this->assertRegExp('/.*sdkversion.*/', $headers['X-PAYPAL-REQUEST-SOURCE']);
	}
}
