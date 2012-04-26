<?php

require_once(dirname(__FILE__) . '/base.php');

class BasicTests extends UnitTestCase {
	public function testCASHInstance() {
		$this->assertIsA(new CASHRequest, 'CASHRequest');
	}

	public function testCoreExists() {
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/CASHConnection.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/CASHDaemon.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/CASHData.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/CASHDBA.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/CASHRequest.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/CASHResponse.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/CASHSystem.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/ElementBase.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/PlantBase.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/SeedBase.php');
	}
	
	public function testEcho() {
		$test_action = 'echo';
		$test_string = 'test echo!';
		$test_request = new CASHRequest(
			array(
				'cash_request_type' => 'echo', 
				'cash_action' => $test_action,
				'string' => $test_string
			)
		);
		$this->assertEqual($test_action, $test_request->response['payload']['cash_action']);
		$this->assertEqual($test_string, $test_request->response['payload']['string']);
	}

    function assertFileExists($filename, $message = '%s') {
        $this->assertTrue(
                file_exists($filename),
                sprintf($message, 'File [$filename] existence check'));
    }
}
?>
