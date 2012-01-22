<?php
require_once('tests/php/base.php');
require_once('framework/php/classes/plants/PeoplePlant.php');

class PeoplePlantTests extends UnitTestCase {	
	function testPeoplePlant(){
		$p = new PeoplePlant('People', array());
		$this->assertIsa($p, 'PeoplePlant');
	}
}

?>
