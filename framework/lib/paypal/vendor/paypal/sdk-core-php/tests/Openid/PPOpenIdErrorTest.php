<?php 

use PayPal\Auth\Openid\PPOpenIdError;
/**
 * Test class for PPOpenIdError.
 *
 */
class PPOpenIdErrorTest extends PHPUnit_Framework_TestCase {
	
	private $error;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->error = new PPOpenIdError();
		$this->error->setErrorDescription('error description')
			->setErrorUri('http://developer.paypal.com/api/error')
			->setError('VALIDATION_ERROR');
	}
	
	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}
	
	/**
	 * @test
	 */
	public function testSerializationDeserialization() {				
		$errorCopy = new PPOpenIdError();
		$errorCopy->fromJson($this->error->toJson());
		
		$this->assertEquals($this->error, $errorCopy);
	}
}