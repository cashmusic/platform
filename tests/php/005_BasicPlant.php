<?php

require_once('tests/php/base.php');
require_once('framework/php/classes/plants/EchoPlant.php');

class CASHPlantTests extends UnitTestCase {

	function testEchoPlant(){
		$eplant = new EchoPlant('blarg',1);
		$this->assertIsa($eplant, 'EchoPlant');
		$output = $eplant->processRequest();
		$this->assertTrue($output);
	}
}
?>
