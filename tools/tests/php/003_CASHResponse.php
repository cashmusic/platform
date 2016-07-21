<?php

require_once(dirname(__FILE__) . '/base.php');

class CASHResponseTests extends UnitTestCase {

	function testCASHResponse(){
		echo "Testing CASHResponse Class\n";

		$cr = new CASHResponse();
		$this->assertIsa($cr, 'CASHResponse');
	}

}
?>
