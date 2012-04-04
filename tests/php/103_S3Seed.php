<?php

require_once('tests/php/base.php');

class S3SeedTests extends UnitTestCase {
	private $s3_connection_id,$s3_key,$s3_bucket,$timestamp;
	
	function __construct() {
		// add a new admin user for this
		$user_add_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'addlogin',
				'address' => 'email@s3test.com',
				'password' => 'thiswillneverbeused',
				'is_admin' => 1
			)
		);
		$this->cash_user_id = $user_add_request->response['payload'];
		
		$this->timestamp = time();
		$this->s3_key = getTestEnv("S3_KEY");
		$this->s3_bucket = getTestEnv("S3_BUCKET");
		
		// add a new connection 
		$c = new CASHConnection($this->cash_user_id); 
		$this->s3_connection_id = $c->setSettings('S3', 'com.amazon',
			array(
				"key" => $this->s3_key, 
				"secret" => getTestEnv("S3_SECRET"), 
				"bucket" => $this->s3_bucket
			) 
		);
	}

	function testS3Seed(){
		if($this->s3_key) {
			$s3 = new S3Seed($this->cash_user_id, $this->s3_connection_id);
			$this->assertIsa($s3, 'S3Seed');
		} else {
			fwrite(STDERR,"S3 credentials not found, skipping tests\n");
			return;
		}
	}

	function testUpload(){
		if($this->s3_key) {
			$s3 = new S3Seed($this->cash_user_id, $this->s3_connection_id);
			
			//create a temp file
			$test_filename = dirname(__FILE__) . '/test' . $this->timestamp;
			$tmp_file = file_put_contents($test_filename,$this->timestamp);
			
			// public file upload:
			$result = $s3->uploadFile($test_filename,false,false);
			$this->assertTrue($result);
			
			// private file upload, custom name
			$result = $s3->uploadFile($test_filename,'test_private' . $this->timestamp);
			$this->assertTrue($result);
			
			// remove the temp file
			unlink(dirname(__FILE__) . '/test' . $this->timestamp);
		} else {
			fwrite(STDERR,"S3 credentials not found, skipping tests\n");
			return;
		}
	}
	
	function testFileList() {
		if($this->s3_key) {
			$s3 = new S3Seed($this->cash_user_id, $this->s3_connection_id);
			
			$full_list = $s3->listAllFiles();
			
			// check for our uploaded keys
			$this->assertTrue(array_key_exists('test' . $this->timestamp,$full_list));
			$this->assertTrue(array_key_exists('test_private' . $this->timestamp,$full_list));
		} else {
			fwrite(STDERR,"S3 credentials not found, skipping tests\n");
			return;
		}
	}

	function testURLsAndContent() {
		if($this->s3_key) {
			$s3 = new S3Seed($this->cash_user_id, $this->s3_connection_id);
			
			// check for the timestamp in the public link for the public test file
			$test_content = CASHSystem::getURLContents('http://' . $this->s3_bucket . '.s3.amazonaws.com/' . 'test' . $this->timestamp);
			$this->assertPattern('/' . $this->timestamp . '/',$test_content);
			
			// and in the private link generated for the private test file
			$test_content = CASHSystem::getURLContents($s3->getExpiryURL('test_private' . $this->timestamp,20));
			$this->assertPattern('/' . $this->timestamp . '/',$test_content);
			
			// now test headers -- relies on fopen wrappers
			if (ini_get('allow_url_fopen')) {
				// first defaults, both present:
				file_get_contents($s3->getExpiryURL('test_private' . $this->timestamp,20));
				$this->assertTrue(array_search('Content-Disposition: attachment', $http_response_header));
				$this->assertTrue(array_search('Cache-Control: no-cache', $http_response_header));
				// no-cache only:
				file_get_contents($s3->getExpiryURL('test_private' . $this->timestamp,20,false,true));
				$this->assertFalse(array_search('Content-Disposition: attachment', $http_response_header));
				$this->assertTrue(array_search('Cache-Control: no-cache', $http_response_header));
				// attachment only:
				file_get_contents($s3->getExpiryURL('test_private' . $this->timestamp,20,true,false));
				$this->assertTrue(array_search('Content-Disposition: attachment', $http_response_header));
				$this->assertFalse(array_search('Cache-Control: no-cache', $http_response_header));
			}
		} else {
			fwrite(STDERR,"S3 credentials not found, skipping tests\n");
			return;
		}
	}

	function testS3AssetSync() {
		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'addasset',
				'title' => 'shouldedit',
				'description' => '',
				'location' => 'test_private' . $this->timestamp,
				'user_id' => $this->cash_user_id,
				'connection_id' => $this->s3_connection_id
			)
		);
		$asset_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'addasset',
				'title' => 'shoulddelete',
				'description' => '',
				'location' => 'thisdoesnotexist',
				'user_id' => $this->cash_user_id,
				'connection_id' => $this->s3_connection_id
			)
		);
		$delta_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'findconnectiondeltas',
				'connection_id' => $this->s3_connection_id
			)
		);
		$deltas = $delta_request->response['payload']['deltas'];
		// should be at least 3 differences...could be more so test >=
		$this->assertTrue(count($deltas) >= 3);
		if (count($deltas)) {
			// test each delta to make sure the correct type of change has been detected:
			$this->assertEqual($deltas['test' . $this->timestamp],'add');
			$this->assertEqual($deltas['test_private' . $this->timestamp],'update');
			$this->assertEqual($deltas['thisdoesnotexist'],'delete');
		}
		
		// order a proper sync
		$sync_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'syncconnectionassets',
				'connection_id' => $this->s3_connection_id
			)
		);
		// test for true return
		$this->assertTrue($sync_request->response['payload']);
		$delta_request = new CASHRequest(
			array(
				'cash_request_type' => 'asset', 
				'cash_action' => 'findconnectiondeltas',
				'connection_id' => $this->s3_connection_id
			)
		);
		// verify there are no remaining deltas to clean up
		$this->assertEqual(count($delta_request->response['payload']['deltas']),0);
	}

	function testDelete() {
		if($this->s3_key) {
			$s3 = new S3Seed($this->cash_user_id, $this->s3_connection_id);
			
			// delete both files and test return
			$result = $s3->deleteFile('test' . $this->timestamp);
			$this->assertTrue($result);
			$result = $s3->deleteFile('test_private' . $this->timestamp);
			$this->assertTrue($result);
			
			// verify they are no longer listed
			$full_list = $s3->listAllFiles();
			$this->assertFalse(array_key_exists('test' . $this->timestamp,$full_list));
			$this->assertFalse(array_key_exists('test_private' . $this->timestamp,$full_list));
		} else {
			fwrite(STDERR,"S3 credentials not found, skipping tests\n");
			return;
		}	
	}
}
