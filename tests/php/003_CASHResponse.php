<?php

require_once('tests/php/base.php');

class CASHResponseTests extends UnitTestCase {

	function testCASHResponse(){
		echo "Testing CASHResponse Class\n";
		
		$cr = new CASHResponse();
		$this->assertIsa($cr, 'CASHResponse');
	}

}
?>
