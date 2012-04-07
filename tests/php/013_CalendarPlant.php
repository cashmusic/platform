<?php
require_once('tests/php/base.php');
require_once('framework/php/classes/plants/CalendarPlant.php');

class CalendarPlantTests extends UnitTestCase {
	var $testingvenue, $testingevent;
	
	function testCalendarPlant(){
		echo "Testing CalendarPlant\n";
		$c = new CalendarPlant('calendar', array());
		$this->assertIsa($c, 'CalendarPlant');
	}

	function testBasicVenue() {
		$venue_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'addvenue',
				'name' => 'Test Name', 
				'city' => 'Test City',
				'region' => 'Test Region'
			)
		);
		$this->assertEqual($venue_request->response['status_code'],200);
		$this->testingvenue = $venue_request->response['payload'];
		
		$venue_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'getvenue',
				'venue_id' => $this->testingvenue
			)
		);
		$this->assertTrue($venue_request->response['payload']);
		if ($venue_request->response['payload']) {
			$this->assertEqual($venue_request->response['payload']['name'],'Test Name');
			$this->assertEqual($venue_request->response['payload']['city'],'Test City');
			$this->assertEqual($venue_request->response['payload']['region'],'Test Region');
		}
		
		unset($venue_request);
	}

	function testBasicEvent() {
		$event_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'addevent', 
				'date' => 1321063200,
				'user_id' => 1,
				'venue_id' => $this->testingvenue,
				'published' => 1,
				'comment' => 'Test Comment'
			)
		);
		$this->assertEqual($event_request->response['status_code'],200);
		$this->testingevent = $event_request->response['payload'];

		$event_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'getevent',
				'event_id' => $this->testingevent
			)
		);
		$this->assertTrue($event_request->response['payload']);
		if ($event_request->response['payload']) {
;			$this->assertEqual($event_request->response['payload']['date'],1321063200);
			$this->assertEqual($event_request->response['payload']['user_id'],'1');
			$this->assertEqual($event_request->response['payload']['venue_id'],$this->testingvenue);
			$this->assertEqual($event_request->response['payload']['published'],'1');
			$this->assertEqual($event_request->response['payload']['comments'],'Test Comment');
		}

		unset($event_request);
	}

	function testEdits() {
		$event_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'editevent',
				'event_id' => $this->testingevent,
				'date' => 1234567891,
				'published' => 0
			)
		);
		$this->assertTrue($event_request->response['payload']);
		if ($event_request->response['payload']) {
			$event_request = new CASHRequest(
				array(
					'cash_request_type' => 'calendar', 
					'cash_action' => 'getevent',
					'event_id' => $this->testingevent
				)
			);
;			$this->assertEqual($event_request->response['payload']['date'],1234567891);
			$this->assertEqual($event_request->response['payload']['published'],'0');
		}

		unset($event_request);
		
		$venue_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'editvenue',
				'venue_id' => $this->testingvenue,
				'name' => 'Edited Name',
				'city' => 'Edited City'
			)
		);
		$this->assertTrue($venue_request->response['payload']);
		if ($venue_request->response['payload']) {
			$venue_request = new CASHRequest(
				array(
					'cash_request_type' => 'calendar', 
					'cash_action' => 'getvenue',
					'venue_id' => $this->testingvenue
				)
			);
			$this->assertEqual($venue_request->response['payload']['name'],'Edited Name');
			$this->assertEqual($venue_request->response['payload']['city'],'Edited City');
		}
		
		unset($venue_request);
	}

	function testDeleteVenue() {
		$event_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'deletevenue', 
				'venue_id' => $this->testingvenue
			)
		);
		// true if deleted successfully
		$this->assertTrue($event_request->response['payload']);
		
		$event_request = new CASHRequest(
			array(
				'cash_request_type' => 'calendar', 
				'cash_action' => 'deletevenue', 
				'venue_id' => $this->testingvenue
			)
		);
		// should be false — deleting a nonexisting venue
		$this->assertFalse($event_request->response['payload']);
	}
}

?>
