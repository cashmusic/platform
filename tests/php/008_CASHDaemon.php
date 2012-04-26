<?php

require_once(dirname(__FILE__) . '/base.php');

class CASHDaemonTests extends UnitTestCase {

	function testCASHDaemonExists() {
		$d = new CASHDaemon();
		$this->assertIsa($d, 'CASHDaemon');
	}
 
	function testRandomize() {
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

	function testGoChance() {
		// Set chance to 100%
		$d = new CASHDaemon(false,100);
		$this->assertTrue($d->go);
		// Set chance to 0%
		$d = new CASHDaemon(false,0);
		$this->assertFalse($d->go);
	}

	function testDestructorLogging() {
		// tests that the destructor fires and logs the run properly
		$d = new CASHDaemon(false,100);
		$daemon_analytics = $d->getAnalytics();
		$last_run = $daemon_analytics['last_run'];
		unset($d);
		$d = new CASHDaemon(false,0);
		$daemon_analytics = $d->getAnalytics();
		$new_last_run = $daemon_analytics['last_run'];
		$this->assertNotEqual($last_run,$new_last_run);
	}
}
?>
