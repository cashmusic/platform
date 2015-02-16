<?php
require_once(dirname(__FILE__) . '/base.php');
require_once(CASH_PLATFORM_ROOT.'/classes/plants/ElementPlant.php');

class CASHPlantTests extends UnitTestCase {

	function testInitializePlant(){
		$cr = new CASHRequest(array());
		$e  = new ElementPlant(42, 0);
		$this->assertIsa($e, 'ElementPlant');
		$output = $e->processRequest();
		$this->assertTrue($output);
	}

	function testFailOnMissingRequirements() {
		// addvenue requires at least a name and a city, leave off city:
		$test_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'addvenue',
				'name' => 'Test Name'
			)
		);
		$this->assertFalse($test_request->response['payload']);
		unset($test_request);
	}
}
?>
