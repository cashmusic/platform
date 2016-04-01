<?php 
use PayPal\Auth\Openid\PPOpenIdAddress;
/**
 * Test class for PPOpenIdAddress.
 *
 */
class PPOpenIdAddressTest extends \PHPUnit_Framework_TestCase {
	
	private $addr;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->addr = self::getTestData();
	}
	
	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}
	
	public static function getTestData() {
		$addr = new PPOpenIdAddress();
		$addr->setCountry("US")->setLocality("San Jose")
		->setPostalCode("95112")->setRegion("CA")
		->setStreetAddress("1, North 1'st street");
		return $addr;
	}
	
	/**
	 * @test
	 */
	public function testSerializationDeserialization() {				
		$addrCopy = new PPOpenIdAddress();
		$addrCopy->fromJson($this->addr->toJson());
		
		$this->assertEquals($this->addr, $addrCopy);
	}
}