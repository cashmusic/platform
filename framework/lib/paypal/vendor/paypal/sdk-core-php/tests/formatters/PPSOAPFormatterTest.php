<?php
use PayPal\Core\PPRequest;
use PayPal\Formatter\PPSOAPFormatter;

class PPSOAPFormatterTest extends PHPUnit_Framework_TestCase {
	
	private $object;
	
	public function setup() {
		$this->object = new PPSOAPFormatter();
	}
	
	/**
	 * @test
	 */
	public function testSimpleSerializationCall() {
		$data = new MockSOAPObject();
		
		$expected = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"  ><soapenv:Header></soapenv:Header><soapenv:Body>'
					. $data->toXMLString()
					. '</soapenv:Body></soapenv:Envelope>';
		$this->assertEquals($expected,
				$this->object->toString(new PPRequest($data, 'SOAP'))
		);
	}
	
	/**
	 * @test
	 */
	public function testSerializationWithNSCall() {
		$data = new MockSOAPObject();		
		$request = new PPRequest($data, 'SOAP');
		$request->addBindingInfo("namespace", 'ns="http://myns"');
		
		$expected = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" ns="http://myns" ><soapenv:Header></soapenv:Header><soapenv:Body>'
		. $data->toXMLString()
		. '</soapenv:Body></soapenv:Envelope>';
		$this->assertEquals($expected, $this->object->toString($request));
	}
	
	/**
	 * @test
	 */
	public function testSerializationWithCredentialCall() {
		$data = new MockSOAPObject();
		$request = new PPRequest($data, 'SOAP');
		$request->addBindingInfo("namespace", 'ns="http://myns"');
		$request->addBindingInfo("securityHeader", "<abc><xyz>1</xyz></abc>");
		
	
		$expected = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" ns="http://myns" ><soapenv:Header><abc><xyz>1</xyz></abc></soapenv:Header><soapenv:Body>'
		. $data->toXMLString()
		. '</soapenv:Body></soapenv:Envelope>';
		$this->assertEquals($expected, $this->object->toString($request));
	}
	
	/**
	 * @test
	 */
	public function testInvalidCall() {
		$this->setExpectedException('BadMethodCallException');
		$this->object->toObject('somestring');
	}
}

class MockSOAPObject {
	public function toXMLString() {
		return '<dummy>XML string</dummy>';
	}
}