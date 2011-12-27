<?php

require_once('tests/php/base.php');

class CASHDaemonTests extends UnitTestCase {

	function testCASHDaemonExists() {
		$d = new CASHDaemon();
		$this->assertIsa($d, 'CASHDaemon');
	}
 
	function testCASHDaemonRandomizes() {
		// tests to make sure that CASHDaemon is choosing a random number 
		// properly on init
		$d = new CASHDaemon();
		$base_val = $d->lottery_val;
		$loop_count = 0;
		while ($loop_count <= 8) {
			$d = new CASHDaemon();
			if ($base_val != $d->lottery_val) {
				$this->pass();
				return true;
			}
			$loop_count++;
		}
		$this->fail();
		return false;
	}

}
?>
