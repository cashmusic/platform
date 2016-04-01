<?php
use PayPal\Core\PPXmlFaultMessage;
use PayPal\Core\PPXmlMessage;
use PayPal\Core\PPUtils;

class SimpleXMLTestClass extends PPXmlMessage {
	/**
	 *
	 * @access public
	 * @namespace ebl
	 * @var string
	 */
	public $field1;
	
	/**
	 *
	 * @access public
	 * @namespace ebl
	 * @var string
	 */
	public $field2;
	
	/**
	 * @name fieldWith-FunnyName
	 * @access public
	 * @namespace ebl
	 * @var string
	 */
	public $fieldWithFunnyName;
}

class SimpleContainerXMLTestClass extends PPXmlMessage {
	
	/**
	 * @access public
	 * @namespace ebl
	 * @var string
	 */
	public $field1;
	
	/**
	 * @array
	 * @access public
	 * @namespace ebl
	 * @var string
	 */
	public $list1;
	
	/**
	 * @array
	 * @access public
	 * @namespace ebl
	 * @var SimpleXMLTestClass
	 */
	public $list2;
	
	/**
	 * @array
	 * @access public
	 * @namespace ebl
	 * @var AttributeXMLTestClass
	 */
	public $list3;
	
	/**
	 * @access public
	 * @namespace ebl
	 * @var SimpleXMLTestClass
	 */
	public $nestedField;
}

class AttributeXMLTestClass extends PPXmlMessage {
	
	/**
	 *
	 * @access public
	 * @attribute
	 * @var string
	 */
	public $attrib1;

	/**
	 *
	 * @access public
	 * @attribute
	 * @var string
	 */
	public $attrib2;
	
	/**
	 *
	 * @access public
	 * @namespace ebl
	 * @value
	 * @var string
	 */
	public $value;
	
}

class AttributeComplexXMLTestClass extends PPXmlMessage {
	
	/**
	 *
	 * @access public
	 * @attribute
	 * @var string
	 */
	public $attrib1;

	/**
	 *
	 * @access public
	 * @attribute
	 * @var string
	 */
	public $attrib2;
	
	/**
	 *
	 * @access public
	 * @var string
	 */
	public $value1;
	
	/**
	 *
	 * @access public
	 * @var string
	 */
	public $value2;
	
}


/**
 * @hasAttribute
 *
 */
class AttributeContainerXMLTestClass extends PPXmlMessage {
	/**
	 *
	 * @access public
	 * @namespace ebl
	 * @var AttributeXMLTestClass
	 */
	public $member;
	
		/**
	 * 
     * @array
	 * @access public
	 * @namespace ebl
	 * @var AttributeXMLTestClass
	 */ 
	public $arrayMember;

}

class FaultDetailsType extends PPXmlMessage {

    /**
     * @access public
     * @namespace ebl
     * @var string
     */
    public $ErrorCode;

    /**
     * @access public
     * @namespace ebl
     * @var string
     */
    public $Severity;

    /**
     *
     * @access public
     * @namespace ebl
     * @var string
     */
    public $DetailedMessage;


}

class FaultMessage extends PPXmlFaultMessage {

    /**
     *
     * @access public
     * @namespace
     * @var ResponseEnvelope
     */
    public $responseEnvelope;

    /**
     *
     * @array
     * @access public
     * @namespace
     * @var ErrorData
     */
    public $error;

}

class ResponseEnvelope extends PPXmlMessage {

    /**
     *
     * @access public
     * @namespace
     * @var string
     */
    public $timestamp;

    /**
     * @access public
     * @namespace
     * @var string
     */
    public $ack;

    /**
     *
     * @access public
     * @namespace
     * @var string
     */
    public $correlationId;

    /**
     *
     * @access public
     * @namespace
     * @var string
     */
    public $build;
}

class ErrorData extends PPXmlMessage {

    /**
     *
     * @access public
     * @namespace
     * @var Long
     */
    public $errorId;

    /**
     *
     * @access public
     * @namespace
     * @var string
     */
    public $domain;

    /**
     *
     * @access public
     * @namespace
     * @var string
     */
    public $subdomain;

    /**
     *
     * @access public
     * @namespace
     * @var string
     */
    public $severity;

    /**
     *
     * @access public
     * @namespace
     * @var string
     */
    public $category;

    /**
     *
     * @access public
     * @namespace
     * @var string
     */
    public $message;

    /**
     *
     * @access public
     * @namespace
     * @var string
     */
    public $exceptionId;

    /**
     *
     * @array
     * @access public
     * @namespace
     * @var ErrorParameter
     */
    public $parameter;
}

class ErrorParameter extends PPXmlMessage {

    /**
     *
     * @access public
     * @namespace common
     * @attribute
     * @var string
     */
    public $name;

    /**
     *
     * @access public
     * @value
     * @var string
     */
    public $value;
}

/**
 * Test class for PPXmlMessage.
 *
 */
class PPXmlMessageTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		
	}
	
	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
	}
	
	
	private function wrapInSoapMessage($str) {
		$str = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:cc="urn:ebay:apis:CoreComponentTypes" xmlns:wsu="http://schemas.xmlsoap.org/ws/2002/07/utility" xmlns:saml="urn:oasis:names:tc:SAML:1.0:assertion" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/12/secext" xmlns:ed="urn:ebay:apis:EnhancedDataTypes" xmlns:ebl="urn:ebay:apis:eBLBaseComponents" xmlns:ns="urn:ebay:api:PayPalAPI">'
			. '<SOAP-ENV:Body id="_0">'
    		. $str
    		. '</SOAP-ENV:Body></SOAP-ENV:Envelope>';
		return PPUtils::xmlToArray($str);
	}

	/**
	 * @test
	 */
	public function testToSOAP() {
		$o = new SimpleXMLTestClass();
		$o->field1 = "fieldvalue1";
		$o->field2 = "fieldvalue2";
		
		$this->assertEquals($o->toXMLString(), $o->toSOAP());
	}
	
	/**
	 * @test
	 */
	public function testSimpleSerialization() {
		
		$o = new SimpleXMLTestClass();
		$o->field1 = "fieldvalue1";
		$o->field2 = "fieldvalue2";

		$this->assertEquals("<ebl:field1>fieldvalue1</ebl:field1><ebl:field2>fieldvalue2</ebl:field2>", $o->toXMLString(''));
	}
	
	/**
	 * @test
	 */
	public function testSpecialCharsSerialization() {
	
		$o = new SimpleXMLTestClass();
		$o->field1 = "fieldvalue1";
		$o->fieldWithFunnyName = "fieldvalue2";
	
		$this->assertEquals("<ebl:field1>fieldvalue1</ebl:field1><ebl:fieldWith-FunnyName>fieldvalue2</ebl:fieldWith-FunnyName>", $o->toXMLString(''));
	}
	
	/**
	 * @test
	 */
	public function testNestedSerialization() {
	
		$child = new SimpleXMLTestClass();
		$child->field1 = "fieldvalue1";
		$child->field2 = "fieldvalue2";
		
		$parent = new SimpleContainerXMLTestClass();
		$parent->field1 = 'parent';
		$parent->nestedField = $child;
		
		
		$this->assertEquals("<ebl:field1>parent</ebl:field1><ebl:nestedField><ebl:field1>fieldvalue1</ebl:field1><ebl:field2>fieldvalue2</ebl:field2></ebl:nestedField>", $parent->toXMLString(''));
	}
	
	/**
	 * @test
	 */
	public function testListSerialization() {	
		
		$parent = new SimpleContainerXMLTestClass();
		$parent->list1 = array('i', 'am', 'an array');	
		
		$this->assertEquals('<ebl:list1>i</ebl:list1><ebl:list1>am</ebl:list1><ebl:list1>an array</ebl:list1>', $parent->toXMLString(''));
		
		
		$child1 = new SimpleXMLTestClass();
		$child1->field1 = "c1v1";
		$child1->field2 = "c1v2";
		$child2 = new SimpleXMLTestClass();
		$child2->field1 = "c2v1";
		$child2->field2 = "c2v2";
		$parent->list2 = array($child1, $child2);
		
		$this->assertEquals('<ebl:list1>i</ebl:list1><ebl:list1>am</ebl:list1><ebl:list1>an array</ebl:list1>'
				. '<ebl:list2><ebl:field1>c1v1</ebl:field1><ebl:field2>c1v2</ebl:field2></ebl:list2>'
				. '<ebl:list2><ebl:field1>c2v1</ebl:field1><ebl:field2>c2v2</ebl:field2></ebl:list2>'
			, $parent->toXMLString(''));
	}

	/**
	 * @test
	 */
	public function testAttributeSerialization() {
	
		$o = new AttributeXMLTestClass();
		$o->attrib1 = "a value";
		$o->attrib2 = "another value";
		$o->value = "value";
		
		$this->assertEquals('attrib1="a value" attrib2="another value">value', $o->toXMLString(''));
		
	
		$o = new AttributeXMLTestClass();		
		$o->value = "value";
		
		$this->assertEquals(' >value', $o->toXMLString(''));
		
		$o = new AttributeXMLTestClass();
		$o->attrib1 = "a value";
		$o->attrib2 = "another value";
		
		$this->assertEquals('attrib1="a value" attrib2="another value">', $o->toXMLString(''));

		$o = new AttributeXMLTestClass();
		$o->attrib1 = "a value";
		$o->attrib2 = "another value";
		$o->value = "value";
		
		$this->assertEquals('attrib1="a value" attrib2="another value">value', $o->toXMLString());
		
		$o = new AttributeComplexXMLTestClass();
		$o->attrib1 = "a value";
		$o->attrib2 = "another value";
		$o->value1 = "value1";
		$o->value2 = "value2";
		
		$this->assertEquals('attrib1="a value" attrib2="another value"><ebl:value1>value1</ebl:value1><ebl:value2>value2</ebl:value2>', $o->toXMLString());

	}
	
	/**
	 * @test
	 */
	public function testSimpleDeserialization() {
		
		$str = $this->wrapInSoapMessage("<SimpleXMLTestClass><field1>fieldvalue1</field1><field2>0</field2></SimpleXMLTestClass>");
		
		$o = new SimpleXMLTestClass();
		$o->init($str);
		
		$this->assertEquals("fieldvalue1", $o->field1);
		$this->assertSame("0", $o->field2);

	}
	
	/**
	 * @test
	 */
	public function testSpecialCharsDeserialization() {
	
		$str = $this->wrapInSoapMessage("<SimpleXMLTestClass><field1>fieldvalue1</field1><fieldWith-FunnyName>fieldvalue2</fieldWith-FunnyName></SimpleXMLTestClass>");
		$o = new SimpleXMLTestClass();
		$o->init($str);
		
		$this->assertEquals('fieldvalue1', $o->field1);
		$this->assertEquals('fieldvalue2', $o->fieldWithFunnyName);
	}
	
	/**
	 * @test
	 */
	public function testAttributeDeserialization() {
	
		$str = $this->wrapInSoapMessage('<AttributeContainerXMLTestClass><member attrib1="a value" attrib2="another value">value</member></AttributeContainerXMLTestClass>');
		$o = new AttributeContainerXMLTestClass();
		$o->init($str);
	
		$this->assertNotNull($o->member);
		$this->assertEquals("value", $o->member->value);
		$this->assertEquals("a value", $o->member->attrib1);
		$this->assertEquals("another value", $o->member->attrib2);
		
		
		$str = $this->wrapInSoapMessage('<AttributeContainerXMLTestClass><member>value</member></AttributeContainerXMLTestClass>');
		$o = new AttributeContainerXMLTestClass();
		$o->init($str);		
		
		$this->assertNotNull($o->member);
// 		$this->assertEquals("value", $o->member->value);
// 		$this->assertNull($o->member->attrib1);
// 		$this->assertNull($o->member->attrib2);
	
	}
	
	/**
	 * @test
	 */
	public function testListDeserialization() {
		
		
		
		$str = $this->wrapInSoapMessage('<SimpleContainerXMLTestClass><list1>i</list1><list1>am</list1><list1>an array</list1></SimpleContainerXMLTestClass>');
		$parent = new SimpleContainerXMLTestClass();
		$parent->init($str);
		
		$this->assertNotNull($parent->list1);
 		$this->assertEquals(true, is_array($parent->list1));
 		$this->assertEquals(3, count($parent->list1));
 		$this->assertEquals('an array', $parent->list1[2]);
		
		
		$str = $this->wrapInSoapMessage('<SimpleContainerXMLTestClass><list1>i</list1><list1>am</list1><list1>an array</list1>'
				. '<list2><field1>c1v1</field1><field2>c1v2</field2></list2>'
				. '<list2><field1>c2v1</field1><field2>c2v2</field2></list2></SimpleContainerXMLTestClass>');
		$parent = new SimpleContainerXMLTestClass();
		$parent->init($str);
		
		$this->assertNotNull($parent->list2);
		$this->assertEquals(true, is_array($parent->list2));
		$this->assertEquals(2, count($parent->list2));
		$this->assertEquals('SimpleXMLTestClass', get_class($parent->list2[0]));
		$this->assertEquals('c1v2', $parent->list2[0]->field2);
		$this->assertEquals('c2v1', $parent->list2[1]->field1);
	}
	
	/**
	 * @test
	 */
	public function testNestedDeserialization() {
		
		$str = $this->wrapInSoapMessage("<SimpleContainerXMLTestClass><field1>parent</field1><nestedField><field1>fieldvalue1</field1><field2>fieldvalue2</field2></nestedField></SimpleContainerXMLTestClass>");
		$o = new SimpleContainerXMLTestClass();
		$o->init($str);
		
		$this->assertEquals("parent", $o->field1);
		$this->assertNotNull($o->nestedField);
		$this->assertEquals("fieldvalue1", $o->nestedField->field1);
		$this->assertEquals("fieldvalue2", $o->nestedField->field2);
		
	}
	
	
	/**
	 * @test
	 */
	public function testSoapFaults() {
		$xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"><soapenv:Header/><soapenv:Body> <soapenv:Fault xmlns:axis2ns237961="http://schemas.xmlsoap.org/soap/envelope/"><faultcode>axis2ns237961:Server</faultcode><faultstring>Authentication failed. API credentials are incorrect.</faultstring><detail><ns3:FaultMessage xmlns:ns3="http://svcs.paypal.com/types/common" xmlns:ns2="http://svcs.paypal.com/types/ap"><responseEnvelope><timestamp>2013-09-03T04:36:14.931-07:00</timestamp><ack>Failure</ack><correlationId>ebeb480862a99</correlationId><build>6941298</build></responseEnvelope><error><errorId>520003</errorId><domain>PLATFORM</domain><subdomain>Application</subdomain><severity>Error</severity><category>Application</category><message>Authentication failed. API credentials are incorrect.</message></error></ns3:FaultMessage></detail></soapenv:Fault></soapenv:Body></soapenv:Envelope>';

		$map = PPUtils::xmlToArray($xml);

		$o = new FaultMessage();
		$o->init($map, false);
		
		$this->assertEquals("Failure", $o->responseEnvelope->ack);
		$this->assertEquals("Application", $o->error[0]->category);
	}
	
}
