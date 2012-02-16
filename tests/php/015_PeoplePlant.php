<?php
require_once('tests/php/base.php');
require_once('framework/php/classes/plants/PeoplePlant.php');

class PeoplePlantTests extends UnitTestCase {	
	var $testing_list;
	
	function testPeoplePlant(){
		$p = new PeoplePlant('People', array());
		$this->assertIsa($p, 'PeoplePlant');
	}
	
	function testAddList() {
		$list_add_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'addlist',
				'name' => 'Test List',
				'description' => 'Test Description',
				'user_id' => 1,
			)
		);
		// should work fine with no description or connection_id
		$this->assertTrue($list_add_request->response['payload']);
		$this->testing_list = $list_add_request->response['payload'];
		
		$list_add_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'addlist',
				'connection_id' => 0,
				'user_id' => 1,
			)
		);
		// should fail with no name
		$this->assertFalse($list_add_request->response['payload']);
	}

	function testGetList() {
		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getlist',
				'list_id' => $this->testing_list
			)
		);
		$this->assertEqual($list_request->response['payload']['name'],'Test List');
	}

	function testEditList() {
		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'editlist',
				'list_id' => $this->testing_list,
				'name' => 'New List Name',
				'description' => 'New List Description',
				'connection_id' => '322'
			)
		);
		$this->assertTrue($list_request->response['payload']);
		
		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getlist',
				'list_id' => $this->testing_list
			)
		);
		if ($list_request->response['payload']) {
			$this->assertEqual($list_request->response['payload']['name'],'New List Name');
			$this->assertEqual($list_request->response['payload']['description'],'New List Description');
			$this->assertEqual($list_request->response['payload']['connection_id'],'322');
		}
	}

	function testDeleteList() {
		// delete it
		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'deletelist',
				'list_id' => $this->testing_list
			)
		);
		$this->assertTrue($list_request->response['payload']);
		
		// test that it's really gone
		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getlist',
				'list_id' => $this->testing_list
			)
		);
		$this->assertFalse($list_request->response['payload']);
	}

	function testSignupRemove() {
		$test_address = 'whatever@cashmusic.org';
		$add_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'addaddresstolist',
				'address' => $test_address,
				'list_id' => $this->testing_list,
				'do_not_verify' => true,
			)
		);
		$this->assertTrue($add_request->response['payload']);

		$list_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getaddresslistinfo',
				'address' => $test_address,
				'list_id' => $this->testing_list,
			)
		);
		// make sure that the address has been added to the local list
		$this->assertTrue($list_request->response['payload']);

		$remove_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'removeaddress',
				'address' => $test_address,
				'list_id' => $this->testing_list
			)
		);
		$this->assertTrue($remove_request->response['payload']);

	}
}

?>
