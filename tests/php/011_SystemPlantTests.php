<?php

require_once(dirname(__FILE__) . '/base.php');
require_once('framework/classes/plants/SystemPlant.php');

class SystemPlantTests extends UnitTestCase {
	function testSystemPlant() {
		echo "Testing SystemPlant\n";
		
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

	function testAddNewLoginData() {
		// admin user:
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'addlogin',
				'address' => 'anotherperson@test.com', 
				'password' => 'testpassword',
				'is_admin' => 0,
				'data' => array('when'=>'now')
			)
		);
		$this->assertTrue($login_request->response['payload']);
		$user_id = $login_request->response['payload'];

		$user_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getuser',
				'user_id' => $user_id
			)
		);
		if ($user_request->response['payload']) {
			$this->assertTrue(is_array($user_request->response['payload']['data']));
			$this->assertEqual($user_request->response['payload']['data']['when'],'now');
		}
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
		// did it work?
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

		// test some username bullshit
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'setlogincredentials',
				'user_id' => '2',
				'username' => 'somethingunique'
			)
		);
		$this->assertTrue($login_request->response['payload']);

		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'addlogin',
				'address' => 'testanotheradminwhocares@test.com', 
				'password' => 'testpassword',
				'username' => 'whocares',
				'is_admin' => 1
			)
		);
		$this->assertTrue($login_request->response['payload']);
		$new_user_id = $login_request->response['payload'];

		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'setlogincredentials',
				'user_id' => '2',
				'username' => 'whocares'
			)
		);
		$user_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getuser',
				'user_id' => '2'
			)
		);
		if ($user_request->response['payload']) {
			// this should have failed so make sure the username is unchanged
			$this->assertEqual($user_request->response['payload']['username'],'somethingunique');
		}

		// now remove the new user
		$delete_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'deletelogin',
				'address' => 'testanotheradminwhocares@test.com'
			)
		);

		// and try renaming again
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'setlogincredentials',
				'user_id' => '2',
				'username' => 'whocares'
			)
		);
		$user_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getuser',
				'user_id' => '2'
			)
		);
		if ($user_request->response['payload']) {
			// with the deleted account it should work now
			$this->assertEqual($user_request->response['payload']['username'],'whocares');
		}

		// now just make sure the new one is unset
		$user_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getuser',
				'user_id' => $new_user_id
			)
		);
		if ($user_request->response['payload']) {
			// with the deleted account it should work now
			$this->assertEqual($user_request->response['payload']['username'],'');
		}
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
				'value' => array('testkey' => 'testval','second' => 'value'),
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
			$this->assertEqual($settings_request->response['payload']['testkey'],'testval');
			$this->assertEqual($settings_request->response['payload']['second'],'value');
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
		$this->assertEqual($settings_request->response['payload'],'we changed it!');

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

	function testTemplates() {
		// first test creating a new template
		$template_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'settemplate',
				'type' => 'page',
				'template' => '<html>Look mom, I\'m a template!</html>',
				'user_id' => 1
			)
		);
		$this->assertTrue($template_request->response['payload']);
		$template_id = $template_request->response['payload'];
		// now test getting that template
		$template_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'gettemplate',
				'template_id' => $template_id
			)
		);
		$this->assertTrue($template_request->response['payload']);
		// make sure template values were set correctly
		if ($template_request->response['payload']) {
			$this->assertEqual($template_request->response['payload'],'<html>Look mom, I\'m a template!</html>');
		}
		// now try getting the newest template
		$template_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'getnewesttemplate',
				'user_id' => 1
			)
		);
		$this->assertTrue($template_request->response['payload']);
		// make sure template values were set correctly
		if ($template_request->response['payload']) {
			$this->assertEqual($template_request->response['payload'],'<html>Look mom, I\'m a template!</html>');
		}
		// try a delete with a bad user id...should fail
		$template_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'deletetemplate',
				'template_id' => $template_id,
				'user_id' => 9876543
			)
		);
		$this->assertFalse($template_request->response['payload']);
		// now try it with the right user id...should succeed (no user id forces delete)
		$template_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'deletetemplate',
				'template_id' => $template_id,
				'user_id' => 1
			)
		);
		$this->assertTrue($template_request->response['payload']);
		// now add a new template to work with
		$template_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'settemplate',
				'type' => 'page',
				'template' => '<html>Look mom, I\'m a template!</html>',
				'user_id' => 1
			)
		);
		$template_id = $template_request->response['payload'];
		// overwrite template
		$template_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'settemplate',
				'type' => 'page',
				'template' => '<html>Look mom, even spéciäl characters!¡</html>',
				'template_id' => $template_id,
				'user_id' => 1
			)
		);
		$this->assertTrue($template_request->response['payload']);
		$template_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'gettemplate',
				'template_id' => $template_id
			)
		);
		// make sure template values were set correctly
		if ($template_request->response['payload']) {
			$this->assertEqual($template_request->response['payload'],'<html>Look mom, even spéciäl characters!¡</html>');
		}
		// so far so good. force delete it by omitting user id
		$template_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'deletetemplate',
				'template_id' => $template_id
			)
		);
		$this->assertTrue($template_request->response['payload']);
	}

	function testAccountDeletion() {
		$login_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'addlogin',
				'address' => 'testingdeletion@test.com', 
				'password' => 'testpassword',
				'username' => 'testingdeletion',
				'is_admin' => 1
			)
		);
		$this->assertTrue($login_request->response['payload']);
		$user_id = $login_request->response['payload'];

		$user_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getuser',
				'user_id' => $user_id
			)
		);
		$this->assertEqual($user_request->response['payload']['username'],'testingdeletion');
		$this->assertNotEqual($user_request->response['payload']['api_key'],'');
		$this->assertNotEqual($user_request->response['payload']['api_secret'],'');

		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'addasset',
				'title' => 'test title',
				'description' => '',
				'location' => 'http://test.com/file',
				'user_id' => $user_id
			)
		);
		$this->assertTrue($asset_request->response['payload']);

		$element_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'addelement',
				'name' => 'Test name',
				'type' => 'stupidfakeelement',
				'options_data' => array(),
				'user_id' => $user_id
			)
		);
		$this->assertTrue($element_request->response['payload']);

		$list_add_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'addlist',
				'name' => 'Test List',
				'description' => 'Test Description',
				'user_id' => $user_id
			)
		);
		$this->assertTrue($list_add_request->response['payload']);

		$delete_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'deletelogin',
				'address' => 'testingdeletion@test.com'
			)
		);
		$this->assertTrue($delete_request->response['payload']);

		$user_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getuser',
				'user_id' => $user_id
			)
		);
		$this->assertEqual($user_request->response['payload']['is_admin'],0);
		$this->assertEqual($user_request->response['payload']['username'],'');
		$this->assertEqual($user_request->response['payload']['api_key'],'');
		$this->assertEqual($user_request->response['payload']['api_secret'],'');

		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'getassetsforuser',
				'user_id' => $user_id
			)
		);
		$this->assertFalse($asset_request->response['payload']);	

		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'people', 
				'cash_action' => 'getlistsforuser',
				'user_id' => $user_id
			)
		);
		$this->assertFalse($asset_request->response['payload']);	
		
		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'element', 
				'cash_action' => 'getelementsforuser',
				'user_id' => $user_id
			)
		);
		$this->assertFalse($asset_request->response['payload']);
	}
}
?>
