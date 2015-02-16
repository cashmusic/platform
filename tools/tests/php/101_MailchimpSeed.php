<?php

require_once(dirname(__FILE__) . '/base.php');

class MailchimpSeedTests extends UnitTestCase {
	var $test_list_id;
	private $mailchimp_connection_id, 
			$api_list_id=false,
			$api_key=false,
			$cash_user_id=1; // arbitrary user id so settings/queries match
	
	function __construct() {
		echo "Testing MailChimp Seed\n";
		
		// add a new admin user for this
		$user_add_request = new CASHRequest(
			array(
				'cash_request_type' => 'system', 
				'cash_action' => 'addlogin',
				'address' => 'email@thisisjustatest.com',
				'password' => 'thiswillneverbeused',
				'is_admin' => 1
			)
		);
		$this->cash_user_id = $user_add_request->response['payload'];
		
		// add a new connection 
		$this->api_key = getTestEnv("MAILCHIMP_API_KEY");
		$this->api_list_id = getTestEnv("MAILCHIMP_LIST_ID");
		if (!$this->api_key || !$this->api_list_id) {
			echo "Mailchimp api key not found, skipping mailchimp tests\n";
		}
		$c = new CASHConnection($this->cash_user_id); // the '1' sets a user id=1
		$this->mailchimp_connection_id = $c->setSettings('MailChimp', 'com.mailchimp',
			array( "key" => $this->api_key, "list" => $this->api_list_id ) );
		
		if ($this->mailchimp_connection_id) {
			// add a new list
			$list_add_request = new CASHRequest(
				array(
					'cash_request_type' => 'people', 
					'cash_action' => 'addlist',
					'name' => 'Test List',
					'description' => 'Test Description',
					'user_id' => $this->cash_user_id,
					'connection_id' => $this->mailchimp_connection_id
				)
			);
			// should work fine with no description or connection_id
			$this->test_list_id = $list_add_request->response['payload'];
		}
	}

	function testSetIDs() {
		// only run if key / list have been set properly
		if ($this->api_key && $this->api_list_id) {
			// make sure the added connection has been set
			$this->assertTrue($this->mailchimp_connection_id);
			$this->assertTrue($this->test_list_id);
		}
	}

	function testMailchimpSeed(){
		$time = time();
		// only run if key / list have been set properly
		if ($this->api_key && $this->api_list_id) {
			$mc = new MailchimpSeed($this->cash_user_id, $this->mailchimp_connection_id); // the '1' sets a user id=1
			$this->assertIsa($mc, 'MailchimpSeed');
			$this->assertTrue($mc->lists());
			$webhooks = $mc->listWebhooks();
			$this->assertTrue(isset($webhooks));
			$members = $mc->listMembers();
			$this->assertTrue($members);
			$total1 = $members['total'];
			$this->assertTrue($total1);
			$this->assertTrue($members['data'][0]['email'] == 'duke@leto.net');
			$test_email = "dev+$time@cashmusic.org";

			$rc = $mc->listSubscribe($test_email, array('double_optin' => false));
			$this->assertTrue($rc);
			if (!$rc) {
				fwrite(STDERR,"Failed to add $test_email to list " . $this->api_list_id);
				exit(1);
			}
			$members2 = $mc->listMembers();
			$this->assertTrue($members2);
			$this->assertTrue($members2['total'] > $total1 );

			$rc = $mc->listUnsubscribe($test_email);
			$this->assertTrue($rc);
			if (!$rc) {
				fwrite(STDERR,"Failed to remove $test_email from list $test_id");
				exit(1);
			}

			$members3 = $mc->listMembers();
			$this->assertTrue($members3);
			$this->assertTrue($members3['total'] == $total1 );
		} 
	}

	function testMailchimpWebhooks(){
		$time = time();
		// only run if key / list have been set properly
		if ($this->api_key && $this->api_list_id) {
			// A valid 200 OK for an external server (don't rely on true API URL in case of localhost):
			$webhook_test_url = 'http://cashmusic.org/';
			
			$mc = new MailchimpSeed($this->cash_user_id, $this->mailchimp_connection_id);

			$webhooks1 = $mc->listWebhooks();
			$initial_webhooks = count($webhooks1);
			//$this->assertTrue(count($webhooks1) == 0, 'zero webhooks initially');

			$rc        = $mc->listWebhookAdd($webhook_test_url);
			$this->assertTrue($rc);

			$webhooks2 = $mc->listWebhooks();
			$this->assertIsa($webhooks2, 'Array');
			$this->assertTrue($webhooks2);
			$this->assertTrue(count($webhooks2) == $initial_webhooks + 1, 'incorrect webhook count');
			$this->assertTrue($webhooks2[$initial_webhooks]); // using $initial_webhooks as index — zero if none, course corrects to our "new" webhook
			$this->assertTrue($webhooks2[$initial_webhooks]['url']);
			$this->assertEqual($webhook_test_url, $webhooks2[$initial_webhooks]['url'], 'urls do not match');

			$rc        = $mc->listWebhookDel($webhook_test_url);
			$this->assertTrue($rc);

			$webhooks3 = $mc->listWebhooks();
			$this->assertEqual($webhooks1, $webhooks3, 'webhooks get deleted properly');

		}
	}

	function testProcessWebhooks(){
		$time = time();
		// only run if key / list have been set properly
		if ($this->api_key && $this->api_list_id) {
			$data_request = new CASHRequest(
				array(
					'cash_request_type' => 'system', 
					'cash_action' => 'getapicredentials',
					'user_id' => $this->cash_user_id
				)
			);
			$api_credentials = $data_request->response['payload'];
			// valid API url, but likely localhost
			
			$webhook_api_url = CASH_API_URL . '/verbose/people/processwebhook/origin/com.mailchimp/list_id/' . $this->test_list_id . '/api_key/' . $api_credentials['api_key'];
			
			// make sure we're rejecting bad keys
			$bad_webhook_api_url  = CASH_API_URL . '/verbose/people/processwebhook/origin/com.mailchimp/list_id/' . $this->test_list_id . '/api_key/incorrect';
			$response = json_decode(CASHSystem::getURLContents($bad_webhook_api_url,array('sample'=>'data'),true));
			// TODO: this is currently returning 400, we need to get that to 403, but we'll test for not-200 
			//       which at least proves we're not accepting bad keys
			$this->assertNotEqual($response->status_code,200);
			
			$test_address = 'dev+shouldnotsubscribe' . $time . '@cashmusic.org';
			$add_post_data = array(
				"type" => "subscribe", 
				"fired_at" => "2009-03-26 21:35:57", 
				"data" => array (
					"id" => "8a25ff1d98", 
					"list_id" => "a6b5da1054",
					"email" => $test_address, 
					"email_type" => "html", 
					"merges" => null,
					"ip_opt" => "10.20.10.30", 
					"ip_signup" => "10.20.10.30"
				)
			);
			CASHSystem::getURLContents($webhook_api_url,$add_post_data,true);
			$list_request = new CASHRequest(
				array(
					'cash_request_type' => 'people', 
					'cash_action' => 'getaddresslistinfo',
					'list_id' => $this->test_list_id,
					'address' => $test_address
				)
			);
			// make sure that the address has been added to the local list
			$this->assertTrue($list_request->response['payload']);
			
			$remove_post_data = array(
				"type" => "unsubscribe", 
				"fired_at" => "2009-03-26 21:36:52", 
				"data" => array (
					"id" => "8a25ff1d98", 
					"action" => "unsub",
					"reason" => "manual",
					"list_id" => "a6b5da1054",
					"email" => $test_address, 
					"email_type" => "html", 
					"merges" => null,
					"ip_opt" => "10.20.10.30", 
					"ip_signup" => "10.20.10.30"
				)
			);
			CASHSystem::getURLContents($webhook_api_url,$remove_post_data,true);
			$list_request = new CASHRequest(
				array(
					'cash_request_type' => 'people', 
					'cash_action' => 'getaddresslistinfo',
					'list_id' => $this->test_list_id,
					'address' => $test_address
				)
			);
			// now make sure that the address has been removed
			$this->assertEqual($list_request->response['payload']['active'],0);
		}
	}

	function testListAddSync(){
		$time = time();
		// only run if key / list have been set properly
		if ($this->api_key && $this->api_list_id) {
			$test_address = 'dev+testlistaddsync' . $time . '@cashmusic.org';
			$add_request = new CASHRequest(
				array(
					'cash_request_type' => 'people', 
					'cash_action' => 'addaddresstolist',
					'address' => $test_address,
					'list_id' => $this->test_list_id,
					'do_not_verify' => true,
					'service_opt_in' => false
				)
			);
			$this->assertTrue($add_request->response['payload']);
			$mc = new MailchimpSeed($this->cash_user_id, $this->mailchimp_connection_id); 
			$members = $mc->listMembers();
			$member_count = count($members['data']);
			// this is a little weird because it's testing the last member of the subcriber list
			// pretty much *should* work but down the line a recursive search would be better to
			// avoid problems when 2 people test at once.
			$this->assertTrue($members['data'][$member_count - 1]['email'] == $test_address);
			
			$remove_request = new CASHRequest(
				array(
					'cash_request_type' => 'people', 
					'cash_action' => 'removeaddress',
					'address' => $test_address,
					'list_id' => $this->test_list_id
				)
			);
			// test that it's been removed on our end
			$this->assertTrue($remove_request->response['payload']);
			$members = $mc->listMembers();
			// post-add total members - post-remove total members should equal one if it's been
			// removed from the subscribers list correctly on the mailchimp end
			$this->assertEqual($member_count - count($members['data']),1);
		}
	}
}
