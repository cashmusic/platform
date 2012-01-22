<?php
require_once('tests/php/base.php');
require_once('framework/php/classes/plants/ElementPlant.php');

class ElementPlantTests extends UnitTestCase {	
	function testElementPlant(){
		$e = new ElementPlant('element', array());
		$this->assertIsa($e, 'ElementPlant');
	}
}

?>
