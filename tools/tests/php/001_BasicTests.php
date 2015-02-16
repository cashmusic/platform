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

    function assertFileExists($filename, $message = '%s') {
        $this->assertTrue(
			file_exists($filename),
			sprintf($message, 'File [$filename] existence check')
		);
    }
}
?>
