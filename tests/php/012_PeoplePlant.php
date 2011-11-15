<?php

require_once('tests/php/base.php');
require_once('framework/php/classes/plants/PeoplePlant.php');

class PeoplePlantTests extends UnitTestCase {
	function testPeoplePlant(){
		$r = array();
		$p = new PeoplePlant('people', $r);
		$this->assertIsa($p, 'PeoplePlant');

		$p->doListSync(1);
		$this->assertTrue(1, 'Called doListSync');
	}
}

?>
