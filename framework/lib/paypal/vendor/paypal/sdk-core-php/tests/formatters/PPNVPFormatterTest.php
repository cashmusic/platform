<?php
use PayPal\Core\PPRequest;
use PayPal\Formatter\PPNVPFormatter;
class PPNVPFormatterTest extends PHPUnit_Framework_TestCase {
	
	private $object;
	
	public function setup() {
		$this->object = new PPNVPFormatter();
	}
	/**
	 * @test
	 */
	public function testValidSerializationCall() {
		$data = new MockNVPObject();
		$this->assertEquals($data->toNVPString(),
				$this->object->toString(new PPRequest($data, 'NVP'))
		);
	}
	
	/**
	 * @test
	 */
	public function testInvalidCall() {
		$this->setExpectedException('BadMethodCallException');
		$this->object->toObject('somestring');
	}
}

class MockNVPObject {
	public function toNVPString() {
		return 'dummy nvp string';
	}
}