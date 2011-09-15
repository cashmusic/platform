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
		$e = new ElementPlant(42, 69);
		$this->assertIsa($e, 'ElementPlant');
	}
}
?>
