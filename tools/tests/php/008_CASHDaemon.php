<?php

require_once(dirname(__FILE__) . '/base.php');

class CASHDaemonTests extends UnitTestCase {

	function testCASHDaemonExists() {
		$d = new CASHDaemon();
		$this->assertIsa($d, 'CASHDaemon');
	}

	

}
?>
