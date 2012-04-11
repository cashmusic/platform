<?php
require_once(dirname(__FILE__) . '/base.php');
require_once('framework/php/classes/plants/AssetPlant.php');

class AssetPlantTests extends UnitTestCase {
	var $testingAsset;
	
	function testAssetPlant(){
		echo "Testing AssetPlant\n";
		
		$a = new AssetPlant('people', array());
		$this->assertIsa($a, 'AssetPlant');
	}
	
	function testAddAsset() {
		// first test requirements
		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'addasset',
				'title' => 'test title'
			)
		);
		// should have failed with just a title:
		$this->assertFalse($asset_request->response['payload']);
		
		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'addasset',
				'title' => 'test title',
				'description' => '',
				'location' => 'http://test.com/file',
				'user_id' => '1'
			)
		);
		$this->assertTrue($asset_request->response['payload']);
		$this->testingAsset = $asset_request->response['payload'];
	}

	function testGetAsset() {
		// test bad id
		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'getasset',
				'id' => -1
			)
		);
		// should be false
		$this->assertFalse($asset_request->response['payload']);

		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'getasset',
				'id' => $this->testingAsset
			)
		);
		$this->assertTrue($asset_request->response['payload']);
		if ($asset_request->response['payload']) {
			$this->assertEqual($asset_request->response['payload']['title'],'test title');
			$this->assertEqual($asset_request->response['payload']['description'],'');
			$this->assertEqual($asset_request->response['payload']['location'],'http://test.com/file');
			$this->assertEqual($asset_request->response['payload']['user_id'],'1');
		}
	}

	function testEditAsset() {
		// first edit the values
		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'editasset',
				'id' => $this->testingAsset,
				'title' => 'edited title',
				'description' => 'edited description',
				'location' => 'http://test.com/edited',
				'connection_id' => '43',
				'parent_id' => '2',
				'public_status' => '1'
			)
		);
		$this->assertTrue($asset_request->response['payload']);

		// now let's check that the new values stuck
		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'getasset',
				'id' => $this->testingAsset
			)
		);
		$this->assertTrue($asset_request->response['payload']);

		if ($asset_request->response['payload']) {
			$this->assertEqual($asset_request->response['payload']['title'],'edited title');
			$this->assertEqual($asset_request->response['payload']['description'],'edited description');
			$this->assertEqual($asset_request->response['payload']['location'],'http://test.com/edited');
			$this->assertEqual($asset_request->response['payload']['connection_id'],'43');
			$this->assertEqual($asset_request->response['payload']['parent_id'],'2');
			$this->assertEqual($asset_request->response['payload']['public_status'],'1');
		}
	}

	function testGetAssetsForUser() {
		// first test a nonexistant user:
		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'getassetsforuser',
				'user_id' => -1
			)
		);
		$this->assertFalse($asset_request->response['payload']);
		
		// then someone we know has assets assigned to them:
		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'getassetsforuser',
				'user_id' => '1'
			)
		);
		$this->assertTrue($asset_request->response['payload']);
	}

	function testGetAnalytics() {
		// recent assets is an easy test
		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'getassetsforuser',
				'user_id' => '1',
				'analtyics_type' => 'recentlyadded'
			)
		);
		$this->assertTrue($asset_request->response['payload']);
		if ($asset_request->response['payload']) {
			$latest_asset = $asset_request->response['payload'][count($asset_request->response['payload']) - 1];
			// test that the latest asset added is in fact our fried the test asset:
			$this->assertEqual($latest_asset['id'],$this->testingAsset);
			// and make sure that the test asset's modification date has been set in the process:
			$this->assertTrue($latest_asset['modification_date']);
		}
	}
}

?>
