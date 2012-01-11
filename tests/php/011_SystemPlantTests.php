<?php

require_once('tests/php/base.php');
require_once('framework/php/classes/plants/SystemPlant.php');

class SystemPlantTests extends UnitTestCase {

	function testSystemPlant(){
		$eplant = new SystemPlant('blarg',1);
		$this->assertIsa($eplant, 'SystemPlant');
	}

	function testLogin() {
		// standard login
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validatelogin',
				'address' => 'root@localhost', 
				'password' => 'hack_my_gibson'
			)
		);
		$this->assertEqual($login_request->response['payload'],'1');
		unset($login_request);
		
		// test forcing login with verified address
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validatelogin',
				'address' => 'root@localhost', 
				'password' => 'wrong password',
				'verified_address' => true
			)
		);
		$this->assertEqual($login_request->response['payload'],'1');
		unset($login_request);
	}
}
?>
