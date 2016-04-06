<?php

use PayPal\Common\PPModel;

class SimpleModelTestClass extends PPModel {
	/**
	 *
	 * @access public
	 * @param string $field1
	 */
	public function setField1($field1) {
		$this->field1 = $field1;
		return $this;
	}
	
	/**
	 *
	 * @access public
	 * @return string
	 */
	public function getField1() {
		return $this->field1;
	}
	
	/**
	 *
	 * @access public
	 * @param string $field2
	 */
	public function setField2($field2) {
		$this->field2 = $field2;
		return $this;
	}
	
	/**
	 *
	 * @access public
	 * @return string
	 */
	public function getField2() {
		return $this->field2;
	}
	
}



class ContainerModelTestClass extends PPModel {

	/**
	 *
	 * @access public
	 * @param string $field1
	 */
	public function setField1($field1) {
		$this->field1 = $field1;
		return $this;
	}

	/**
	 *
	 * @access public
	 * @return string
	 */
	public function getField1() {
		return $this->field1;
	}

	/**
	 *
	 * @access public
	 * @param SimpleModelTestClass $field1
	 */
	public function setNested1($nested1) {
		$this->nested1 = $nested1;
		return $this;
	}

	/**
	 *
	 * @access public
	 * @return SimpleModelTestClass
	 */
	public function getNested1() {
		return $this->nested1;
	}


}

class ListModelTestClass extends PPModel {

	/**
	 *
	 * @access public
	 * @param string $list1
	 */
	public function setList1($list1) {
		$this->list1 = $list1;
	}

	/**
	 *
	 * @access public
	 * @return string
	 */
	public function getList1() {
		return $this->list1;
	}

	/**
	 *
	 * @access public
	 * @param SimpleModelTestClass $list2 array of SimpleModelTestClass
	 */
	public function setList2($list2) {
		$this->list2 = $list2;
		return $this;
	}

	/**
	 *
	 * @access public
	 * @return SimpleModelTestClass array of SimpleModelTestClass
	 */
	public function getList2() {
		return $this->list2;
	}


}

/**
 * Test class for PPModel.
 *
 */
class PPModelTest extends PHPUnit_Framework_TestCase
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
	public function testSimpleConversion() {
		$o = new SimpleModelTestClass();
		$o->setField1('value 1');
		$o->setField2("value 2");
		
		$this->assertEquals('{"field1":"value 1","field2":"value 2"}', $o->toJSON());
		
		$oCopy = new SimpleModelTestClass();
		$oCopy->fromJson($o->toJSON());		
		$this->assertEquals($o, $oCopy);
				
	}
	
	/**
	 * @test
	 */
	public function testSpecialChars() {
		$o = new SimpleModelTestClass();
		$o->setField1('value "1');
		$o->setField2("value 2");
	
		$this->assertEquals('{"field1":"value \"1","field2":"value 2"}', $o->toJSON());
	
		$oCopy = new SimpleModelTestClass();
		$oCopy->fromJson($o->toJSON());
		$this->assertEquals($o, $oCopy);
	
	}
	
	
	/**
	 * @test
	 */
	public function testNestedConversion() {
		$child = new SimpleModelTestClass();
		$child->setField1('value 1');
		$child->setField2("value 2");
		
		$parent = new ContainerModelTestClass();
		$parent->setField1("parent");
		$parent->setNested1($child);
	
		$this->assertEquals('{"field1":"parent","nested1":{"field1":"value 1","field2":"value 2"}}', 
				$parent->toJSON());
	
		$parentCopy = new ContainerModelTestClass();
		$parentCopy->fromJson($parent->toJSON());
		$this->assertEquals($parent, $parentCopy);
	
	}
	
	
	/**
	 * @test
	 */
	public function testListConversion() {
		$c1 = new SimpleModelTestClass();
		$c1->setField1("a")->setField2('value');
		
		$c2 = new SimpleModelTestClass();
		$c1->setField1("another")->setField2('object');
		
		$parent = new ListModelTestClass();
		$parent->setList1(array('simple', 'list', 'with', 'integer', 'keys'));
		$parent->setList2(array($c1, $c2));
		
		$parentCopy = new ListModelTestClass();
		$parentCopy->fromJson($parent->toJSON());
		$this->assertEquals($parent, $parentCopy);
	
	}
}