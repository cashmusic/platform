<?php
require_once(dirname(__FILE__) . '/base.php');
require_once('framework/php/classes/plants/ElementPlant.php');

class ElementPlantTests extends UnitTestCase {	
	function testElementPlant(){
		echo "Testing ElementPlant\n";
		$e = new ElementPlant('element', array());
		$this->assertIsa($e, 'ElementPlant');
	}
}

?>
