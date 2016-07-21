<?php

require_once(dirname(__FILE__) . '/base.php');

class CASHDBATests extends UnitTestCase {

	function testCASHDBA(){
		echo "Testing CASHDBA Class\n";

		$cdba = new CASHDBA('','','','','');
		$this->assertIsa($cdba, 'CASHDBA');
	}

}
?>
