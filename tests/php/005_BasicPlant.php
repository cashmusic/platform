<?php

require_once('tests/php/base.php');
require_once('framework/php/classes/plants/ElementPlant.php');
require_once('framework/php/classes/plants/EchoPlant.php');
require_once('framework/php/classes/plants/CalendarPlant.php');

class CASHPlantTests extends UnitTestCase {

	function testEchoPlant(){
		$eplant = new EchoPlant('blarg',1);
		$this->assertIsa($eplant, 'EchoPlant');
		$output = $eplant->processRequest();
		$this->assertTrue($output);
	}

	function testElementPlant(){
		$cr = new CASHRequest(array());
		$e  = new ElementPlant(42, 0);
		$this->assertIsa($e, 'ElementPlant');
		$output = $e->processRequest();
		$this->assertTrue($output);

		// element id 1 shouldn't exist yet
		$output = $e->getElement(1);
		$this->assertFalse($output);
	}

	function testCalendarPlant(){
		$cplant = new CalendarPlant('blarg',1);
		$this->assertIsa($cplant, 'CalendarPlant');
	}
	
	function testAddVenue() {
		$test_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'addvenue',
				'name' => 'Test Name', 
				'city' => 'Test City',
				'region' => 'Test Region'
			)
		);
		$this->assertEqual($test_request->response['status_code'],200);
		unset($test_request);
	}

	function testAddEvent() {
		$test_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'addevent', 
				'date' => 1321063200,
				'user_id' => 1,
				'venue_id' => 1,
				'published' => 1,
				'comment' => 'Test Comment'
			)
		);
		$this->assertEqual($test_request->response['status_code'],200);
		unset($test_request);
	}
}
?>
