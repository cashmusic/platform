<?php

require_once('tests/php/base.php');

class CASHDBATests extends UnitTestCase {

	function testCASHDBA(){
		$cdba = new CASHDBA('','','','','');
		$this->assertIsa($cdba, 'CASHDBA');
	}

}
?>
