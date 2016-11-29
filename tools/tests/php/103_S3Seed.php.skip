<?php

require_once(dirname(__FILE__) . '/base.php');

class S3SeedTests extends UnitTestCase {
	private $s3_connection_id,$s3_key,$s3_bucket,$timestamp;

	function __construct() {
		echo "Testing S3 Seed\n";

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

		if (!$this->s3_key) {
			echo "S3 credentials not found, skipping tests\n";
		}

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
		}
	}

	function testFileList() {
		if($this->s3_key) {
			$s3 = new S3Seed($this->cash_user_id, $this->s3_connection_id);

			$full_list = $s3->listAllFiles();

			// check for our uploaded keys
			$this->assertTrue(array_key_exists('test' . $this->timestamp,$full_list));
			$this->assertTrue(array_key_exists('test_private' . $this->timestamp,$full_list));
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
		}
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
		}
	}

	function testAuth() {
		if($this->s3_key) {
			$s3 = new S3Seed($this->cash_user_id, $this->s3_connection_id);

			$starting_acp = ($s3->getAccessControlPolicy($this->s3_bucket));
			$this->assertTrue(is_array($starting_acp));

			$second_email = getTestEnv("S3_2_EMAIL");
			$second_key = getTestEnv("S3_2_KEY");
			$second_secret = getTestEnv("S3_2_SECRET");
			if ($second_email && $second_key && $second_secret) {
				$auth_success = $s3->authorizeEmailForBucket($this->s3_bucket,$second_email);
				$this->assertTrue($auth_success);

				$changed_acp = ($s3->getAccessControlPolicy($this->s3_bucket));

				$this->assertNotEqual($starting_acp,$changed_acp);

				// add a new connection for the second user
				$c = new CASHConnection($this->cash_user_id);
				$new_connection_id = $c->setSettings('S32', 'com.amazon',
					array(
						"key" => $second_key,
						"secret" => $second_secret,
						"bucket" => $this->s3_bucket
					)
				);
				if ($new_connection_id) {
					$s32 = new S3Seed($this->cash_user_id, $new_connection_id);

					// now test that we do in fact have upload permission
					// go through the range of tests — upload, delete, verify
					$test_filename = dirname(__FILE__) . '/test' . $this->timestamp;
					$tmp_file = file_put_contents($test_filename,$this->timestamp);
					$result = $s32->uploadFile($test_filename,false,false);
					$this->assertTrue($result);
					$result = $s32->deleteFile('test' . $this->timestamp);
					$this->assertTrue($result);
					$full_list = $s32->listAllFiles();
					$this->assertFalse(array_key_exists('test' . $this->timestamp,$full_list));
					unlink(dirname(__FILE__) . '/test' . $this->timestamp);
					unset($s32);

					$acp_success = $s3->setAccessControlPolicy($this->s3_bucket,'',$starting_acp);
					$this->assertTrue($acp_success);
					$changed_acp = ($s3->getAccessControlPolicy($this->s3_bucket));
					$this->assertEqual($starting_acp,$changed_acp);
				} else {
					echo 'problem adding second S3Seed';
				}
			}
		}
	}
}
