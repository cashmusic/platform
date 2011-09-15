<?php

require_once('tests/php/base.php');
require_once('framework/php/classes/plants/ElementPlant.php');
require_once('framework/php/classes/plants/EchoPlant.php');

class CASHPlantTests extends UnitTestCase {

	function testEchoPlant(){
		$eplant = new EchoPlant('blarg',1);
		$this->assertIsa($eplant, 'EchoPlant');
		$output = $eplant->processRequest();
		$this->assertTrue($output);
	}

	function testElementPlant(){
		$cr = new CASHRequest(array());
		$e  = new ElementPlant(42, 0);
		$this->assertIsa($e, 'ElementPlant');
		$output = $e->processRequest();
		$this->assertTrue($output);

		// element id 1 shouldn't exist yet
		$output = $e->getElement(1);
		$this->assertFalse($output);
	}
}
?>
