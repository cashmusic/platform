<?php

require_once('tests/php/base.php');

class CASHDBATests extends UnitTestCase {

	function testCASHDBA(){
		echo "Testing CASHDBA Class\n";
		
		$cdba = new CASHDBA('','','','','');
		$this->assertIsa($cdba, 'CASHDBA');
	}

}
?>
