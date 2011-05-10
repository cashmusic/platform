<?php
require_once 'PHPUnit/Autoload.php';
require_once dirname(__FILE__).'/../../core/php/cashmusic.php';

class BasicTests extends PHPUnit_Framework_TestCase {
	public function testCASHInstance() {
		$this->assertInstanceOf('CASHRequest', new CASHRequest);
	}

	public function testCoreExists() {
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/CASHData.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/PlantBase.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/SeedBase.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/CASHRequest.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/CASHResponse.php');
		$this->assertFileExists(CASH_PLATFORM_ROOT.'/classes/core/CASHDBA.php');
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
		$this->assertEquals($test_action, $test_request->response['payload']['cash_action']);
		$this->assertEquals($test_string, $test_request->response['payload']['string']);
	}
}
?>