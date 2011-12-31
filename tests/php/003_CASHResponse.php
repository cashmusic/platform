<?php

require_once('tests/php/base.php');

class CASHResponseTests extends UnitTestCase {

	function testCASHResponse(){
		$cr = new CASHResponse();
		$this->assertIsa($cr, 'CASHResponse');
	}

}
?>
