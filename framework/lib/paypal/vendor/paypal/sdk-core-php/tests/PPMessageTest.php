<?php
use PayPal\Core\PPMessage;
use PayPal\Core\PPUtils;

class SimpleTestClass extends PPMessage {
	/**
	 *
	 * @access public
	 * @var string
	 */
	public $field1;
	
	/**
	 *
	 * @access public
	 * @var string
	 */
	public $field2;
}

class SimpleContainerTestClass extends PPMessage {
	
	/**
	 * @access public
	 * @var string
	 */
	public $field1;
	
	/**
	 * @array
	 * @access public
	 * @var string
	 */
	public $list1;
	
	/**
	 * @array
	 * @access public
	 * @var SimpleTestClass
	 */
	public $list2;
	
	/**
	 * @array
	 * @access public
	 * @var AttributeTestClass
	 */
	public $list3;
	
	/**
	 * @access public
	 * @var SimpleTestClass
	 */
	public $nestedField;
}

class AttributeTestClass extends PPMessage {
	
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
	 * @value
	 * @var string
	 */
	public $value;
	
}

/**
 * @hasAttribute
 *
 */
class AttributeContainerTestClass extends PPMessage {
	/**
	 *
	 * @access public
	 * @var AttributeTestClass
	 */
	public $member;
	
		/**
	 * 
     * @array
	 * @access public
	 * @var AttributeTestClass
	 */ 
	public $arrayMember;

}


/**
 * Test class for PPMessage.
 *
 */
class PPMessageTest extends PHPUnit_Framework_TestCase
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
	
	/**
	 * @test
	 */
	public function attributeSerialization() {
		$o = new AttributeTestClass();
		$o->attrib1 = "abc";
		$o->attrib2 = "random value";
		$c = new AttributeContainerTestClass();
		$c->member = $o;
		
		$this->assertEquals("attrib1=abc&attrib2=random+value", $o->toNVPString());		
		$this->assertEquals("member.attrib1=abc&member.attrib2=random+value", $c->toNVPString());		
		
		$o->value = "value";
		$this->assertEquals("attrib1=abc&attrib2=random+value&=value", $o->toNVPString());
		$this->assertEquals("member.attrib1=abc&member.attrib2=random+value&member=value", $c->toNVPString());

				
	}
	
	/**
	 * @test
	 */
	public function attributeSerializationInArrays() {
		$o = new AttributeTestClass();
		$o->attrib1 = "abc";
		$o->attrib2 = "random value";
		$o->value = "value";
		
		$c = new AttributeContainerTestClass();
		$c->member = $o;
		
		$o = new AttributeTestClass();
		$o->attrib1 = "abc";
		$o->attrib2 = "random value";
		$c->arrayMember = array($o);
		
		$this->assertEquals("member.attrib1=abc&member.attrib2=random+value&member=value&arrayMember(0).attrib1=abc&arrayMember(0).attrib2=random+value", 
				$c->toNVPString());
		
		$c->arrayMember[0]->value = "value";
		$this->assertEquals("member.attrib1=abc&member.attrib2=random+value&member=value&arrayMember(0).attrib1=abc&arrayMember(0).attrib2=random+value&arrayMember(0)=value",
				$c->toNVPString());
		
	}
	
	/**
	 * @test
	 */
	public function attributeDeserialization() {
		
		// Attributes and value present
		$responseMap = array(
			"member.attrib1" => "abc",
			"member.attrib2" => "random+value",
			"member" => "value"
		);	
		$c = new AttributeContainerTestClass();
		$c->init($responseMap);
		
		$this->assertNotNull($c->member);
		$this->assertEquals("abc", $c->member->attrib1);
		$this->assertEquals("random value", $c->member->attrib2);
		$this->assertEquals("value", $c->member->value);

		// Only value present		
		$responseMap = array(
			"member" => "value"
		);		
		$c = new AttributeContainerTestClass();
		$c->init($responseMap);
		
		$this->assertNotNull($c->member);
		$this->assertEquals("value", $c->member->value);
		

		// Only attributes present		
		$responseMap = array(
			"member.attrib1" => "abc",
			"member.attrib2" => "random+value"
		);
		$c = new AttributeContainerTestClass();
		$c->init($responseMap);
		
		$this->assertNotNull($c->member);
		$this->assertEquals("abc", $c->member->attrib1);
		$this->assertEquals("random value", $c->member->attrib2);		
	
	}
	
	/**
	 * @test
	 */
	public function attributeDeserializationInArrays() {
	
		// Only value present. Single item in list
		$responseMap = array(
				"arrayMember(0)" => "value+1"
		);
		$c = new AttributeContainerTestClass();
		$c->init($responseMap);
		$this->assertNotNull($c->arrayMember[0]);
		$this->assertEquals("value 1", $c->arrayMember[0]->value);
		
		
		// Only attributes present. Single item in list
		$responseMap = array(
			"arrayMember(0).attrib1" => "abc",
			"arrayMember(0).attrib2" => "random+value",				
		);
		$c = new AttributeContainerTestClass();
		$c->init($responseMap);
		
		$this->assertNotNull($c->arrayMember[0]);
		$this->assertEquals("abc", $c->arrayMember[0]->attrib1);
		$this->assertEquals("random value", $c->arrayMember[0]->attrib2);		
		
		
		// Attributes and value present. Mulitple items in list
		$responseMap = array(
			"arrayMember(0).attrib1" => "abc",
			"arrayMember(0).attrib2" => "random+value",
			"arrayMember(0)" => "value",
			"arrayMember(0).attrib1" => "xyz",
			"arrayMember(1).attrib1" => "attribute1"
		);
		$c->init($responseMap);

		$this->assertEquals("value", $c->arrayMember[0]->value);
		$this->assertEquals("xyz", $c->arrayMember[0]->attrib1);
		$this->assertEquals("random value", $c->arrayMember[0]->attrib2);
		
		$this->assertEquals("attribute1", $c->arrayMember[1]->attrib1);
		$this->assertNull($c->arrayMember[1]->value);
		
	}


	/**
	 * @test
	 */
	public function simpleSerialization() {
		
		$o = new SimpleTestClass();
		$o->field1 = "fieldvalue1";
		$o->field2 = "fieldvalue2";
		
		$this->assertEquals("field1=fieldvalue1&field2=fieldvalue2", $o->toNVPString(''));
	}
	
	
	/**
	 * @test
	 */
	public function simpleDeserialization() {
	
		$map = array(
			"field1" => "fieldvalue1",
			"field2" => "field+value2"
		);
		$o = new SimpleTestClass();
		$o->init($map);
	
		$this->assertEquals("fieldvalue1", $o->field1);
		$this->assertEquals("field value2", $o->field2);
	}
	
	
	/**
	 * @test
	 */
	public function nestedSerialization() {
	
		$o = new SimpleTestClass();
		$o->field1 = "fieldvalue1";
		$o->field2 = "fieldvalue2";
	
		$c = new SimpleContainerTestClass();
		$c->nestedField = $o;
		$c->field1 = "abc";
	
		$this->assertEquals("field1=abc&nestedField.field1=fieldvalue1&nestedField.field2=fieldvalue2", $c->toNVPString(''));
	}

	
	/**
	 * @test
	 */
	public function nestedDeserialization() {
		
		$map = array(
			"field1" => "abc",
			"nestedField.field1" => "fieldvalue1",
			"nestedField.field2" => "field+value2"
		);
	
		$c = new SimpleContainerTestClass();
		$c->init($map);

		$this->assertEquals("abc", $c->field1);
		$this->assertEquals("fieldvalue1", $c->nestedField->field1);
		$this->assertEquals("field value2", $c->nestedField->field2);
	}
	
	
	/**
	 * @test
	 */
	public function simpleListSerialization() {
	
		$c = new SimpleContainerTestClass();
		$c->list1 = array('Array', "of", "some strings");
		$c->field1 = "abc";
	
		$this->assertEquals("field1=abc&list1(0)=Array&list1(1)=of&list1(2)=some+strings", $c->toNVPString(''));
	}
	
	/**
	 * @test
	 */
	public function simpleListDeserialization() {
	
		$map = array(
			"field1" => "abc",
			"list1(0)" => "Array",
			"list1(1)" => "of",
			"list1(2)" => "some+strings"
		);
				
		$c = new SimpleContainerTestClass();
		$c->init($map);
	
		$this->assertEquals("abc", $c->field1);
		$this->assertEquals(3, count($c->list1));
		$this->assertEquals("some strings", $c->list1[2]);
	}
	
	/**
	 * @test
	 */
	public function complexListSerialization() {
	
		$o1 = new SimpleTestClass();
		$o1->field1 = "somevalue1";
		$o1->field2 = "somevalue2";
	
		$o2 = new SimpleTestClass();
		$o2->field1 = "another value1";
		$o2->field2 = "anothervalue2";
		
		$c = new SimpleContainerTestClass();
		$c->list2 = array($o1, $o2);
	
		$this->assertEquals("list2(0).field1=somevalue1&list2(0).field2=somevalue2&list2(1).field1=another+value1&list2(1).field2=anothervalue2", 
				$c->toNVPString(''));
	}
	
	/**
	 * @test
	 */
	public function complexListDeserialization() {
		
		$map = array(
			"list2(0).field1" => "somevalue1",
			"list2(0).field2" => "somevalue2",
			"list2(1).field1" => "another+value1",
			"list2(1).field2" => "anothervalue2"
		);
		
		$c = new SimpleContainerTestClass();
		$c->init($map);
	
		$this->assertEquals(2, count($c->list2));
		$this->assertEquals("somevalue1", $c->list2[0]->field1);
		$this->assertEquals("another value1", $c->list2[1]->field1);
	}
	
	
	/**
	 * @test
	 */
	public function serializeAndDeserialize() {
		
		$o1 = new AttributeTestClass();
		$o1->value = "some value";
		$o1->attrib1 = "someattrib";
		
		$o2 = new AttributeTestClass();
		$o2->value = "some value2";
		
		$o3 = new AttributeTestClass();
		$o3->attrib1 = "attribute";
		$o3->value = "some value3";
		
		$c = new SimpleContainerTestClass();
		$c->list3 = array($o1, $o2, $o3);
		
		$newC = new SimpleContainerTestClass();
		$newC->init(PPUtils::nvpToMap($c->toNVPString(''))); //TODO: Mock nvpToMap
		
		$this->assertEquals($c, $newC);
	}

	/**
	 * @test
	 */
	public function deserializeAndSerialize() {		
		$nvpString = "list2(0).field1=somevalue1&list2(0).field2=somevalue2&list2(1).field1=another+value1&list2(1).field2=anothervalue2&list3(0).attrib1=somevalue1&list3(0).attrib2=somevalue2&list3(0)=value+field&list3(1).attrib1=another+value1&list3(2)=anothervalue2";
		$newC = new SimpleContainerTestClass();
		$newC->init(PPUtils::nvpToMap($nvpString)); //TODO: Mock nvpToMap		
		$this->assertEquals($nvpString, $newC->toNVPString());
	}
}