<?php

require_once(dirname(__FILE__) . '/base.php');

class CASHDaemonTests extends UnitTestCase {

	function testCASHDaemonExists() {
		$d = new CASHDaemon();
		$this->assertIsa($d, 'CASHDaemon');
	}

	function testRun() {
		$last_run = 0;
		$history_request = new CASHRequest(
			array(
				'cash_request_type' => 'system',
				'cash_action' => 'getsettings',
				'type' => 'daemon',
				'user_id' => -1
			)
		);
		if ($history_request->response['payload']) {
			$last_run = $history_request->response['payload']['last_run'];
		} else {
			for ($i = 1; $i <= 150; $i++) {
				// 150 new CASH Requests should wind up with a daemon spawning
				new CASHRequest();
			}
			$history_request = new CASHRequest(
				array(
					'cash_request_type' => 'system',
					'cash_action' => 'getsettings',
					'type' => 'daemon',
					'user_id' => -1
				)
			);
			if ($history_request->response['payload']) {
				$last_run = $history_request->response['payload']['last_run'];
			}
		}

		// pass the test. it ran in one of the previous CASHRequests
		$this->assertTrue($last_run);
	}

}
?>
