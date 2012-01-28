<?php

require_once('tests/php/base.php');
require_once('framework/php/classes/plants/SystemPlant.php');

class SystemPlantTests extends UnitTestCase {

	function testSystemPlant() {
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

	function testAddNewLogin() {
		// admin user:
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'addlogin',
				'address' => 'testadmin@test.com', 
				'password' => 'testpassword',
				'is_admin' => 1
			)
		);
		$this->assertTrue($login_request->response['payload']);
		$user_id = $login_request->response['payload'];
		
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validatelogin',
				'address' => 'testadmin@test.com', 
				'password' => 'testpassword',
				'require_admin' => true
			)
		);
		$this->assertEqual($login_request->response['payload'],$user_id);
		
		// non-admin user
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'addlogin',
				'address' => 'testnonadmin@test.com', 
				'password' => 'testpassword'
			)
		);
		$this->assertTrue($login_request->response['payload']);
		$user_id = $login_request->response['payload'];
		
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validatelogin',
				'address' => 'testnonadmin@test.com', 
				'password' => 'testpassword',
				'require_admin' => true
			)
		);
		// should fail
		$this->assertFalse($login_request->response['payload']);
	}
	
	function testResetLoginInfo() {
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'setlogincredentials',
				'user_id' => '2',
				'address' => 'changed@test.com', 
				'password' => 'changedpassword'
			)
		);
		// should fail
		$this->assertTrue($login_request->response['payload']);
		
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validatelogin',
				'address' => 'changed@test.com', 
				'password' => 'changedpassword'
			)
		);
		$this->assertTrue($login_request->response['payload']);
	}

	function testAPICredentials() {
		$credentials_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'setapicredentials',
				'user_id' => '1'
			)
		);
		$this->assertTrue($credentials_request->response['payload']);
		$credentials = $credentials_request->response['payload'];

		$credentials_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'getapicredentials',
				'user_id' => '1'
			)
		);
		$this->assertTrue($credentials_request->response['payload']);
		// make sure the results of set and get are identical:
		$this->assertEqual($credentials_request->response['payload'],$credentials);
		
		// test calidating the credentials
		$credentials_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validateapicredentials',
				'api_key' => $credentials['api_key']
			)
		);
		$this->assertTrue($credentials_request->response['payload']);
		if ($credentials_request->response['payload']) {
			// successful. make sure that auth_type has been set to api_key for
			// key-only authorization attempt
			$this->assertEqual($credentials_request->response['payload']['auth_type'],'api_key');
			// and that we have the correct user id
			$this->assertEqual($credentials_request->response['payload']['user_id'],'1');
		}

		// test validating the credentials
		$credentials_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'validateapicredentials',
				'api_key' => $credentials['api_key'],
				'api_secret' => $credentials['api_secret']
			)
		);
		$this->assertTrue($credentials_request->response['payload']);
		if ($credentials_request->response['payload']) {
			// successful. make sure that auth_type has been set to api_fullauth
			$this->assertEqual($credentials_request->response['payload']['auth_type'],'api_fullauth');
			// and that we have the correct user id
			$this->assertEqual($credentials_request->response['payload']['user_id'],'1');
		}
		unset($credentials_request);
	}

	function testSettings() {
		// first test creating a new setting
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'setsettings',
				'type' => 'tests',
				'value' => json_encode(array('testkey' => 'testval','second' => 'value')),
				'user_id' => 1
			)
		);
		$this->assertTrue($settings_request->response['payload']);
		// now test getting that setting
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'getsettings',
				'type' => 'tests',
				'user_id' => 1
			)
		);
		$this->assertTrue($settings_request->response['payload']);
		// make sure values were set correctly
		if ($settings_request->response['payload']) {
			$decoded = json_decode($settings_request->response['payload']['value'],true);
			$this->assertEqual($decoded['testkey'],'testval');
			$this->assertEqual($decoded['second'],'value');
		}

		// test overwriting with a new value by key/user
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'setsettings',
				'type' => 'tests',
				'value' => 'we changed it!',
				'user_id' => 1
			)
		);
		$this->assertTrue($settings_request->response['payload']);
		// now test the new values have been set correctly
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'getsettings',
				'type' => 'tests',
				'user_id' => 1
			)
		);
		$this->assertEqual($settings_request->response['payload']['value'],'we changed it!');

		// okay, blow it away and make sure it's gone
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'deletesettings',
				'type' => 'tests',
				'user_id' => 1
			)
		);
		$this->assertTrue($settings_request->response['payload']);
		$settings_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'getsettings',
				'type' => 'tests',
				'user_id' => 1
			)
		);
		$this->assertFalse($settings_request->response['payload']);
	}
}
?>
