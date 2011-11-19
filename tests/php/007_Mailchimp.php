<?php

require_once('tests/php/base.php');

class MailchimpTests extends UnitTestCase {
	private $mailchimp_settings_id, 
			$test_id='b607c6d911', // an already-created list for testing
			$cash_user_id=1, // arbitrary user id so settings/queries match
			$api_key=false;
	
	function __construct() {
		$this->api_key = getenv("MAILCHIMP_API_KEY");
		$c = new CASHSettings($this->cash_user_id); // the '1' sets a user id=1
		$this->mailchimp_settings_id = $c->setSettings('MailChimp', 'com.mailchimp',
			array( "key" => $this->api_key, "list" => $this->test_id ) );
	}
	
	function testMailchimpSeed(){
		$time = time();
		if($this->api_key) {
			$mc = new MailchimpSeed($this->cash_user_id, $this->mailchimp_settings_id); // the '1' sets a user id=1
			$this->assertIsa($mc, 'MailchimpSeed');
			$this->assertTrue($mc->url);
			$this->assertTrue($mc->lists());
			$webhooks = $mc->listWebhooks();
			$this->assertTrue(isset($webhooks));
			$members = $mc->listMembers();
			$this->assertTrue($members);
			$total1 = $members['total'];
			$this->assertTrue($total1);
			$this->assertTrue($members['data'][0]['email'] == 'duke@leto.net');
			$test_email = "duke$time@cashmusic.org";

			$rc = $mc->listSubscribe($test_email, null, null, $optin=false);
			$this->assertTrue($rc);
			if (!$rc) {
				fwrite(STDERR,"Failed to add $test_email to list $test_id");
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
		} else {
			fwrite(STDERR,"Mailchimp api key not found, skipping mailchimp tests\n");
			return;
		}
	}
	function testMailchimpWebhooks(){
		$time = time();
		if($this->api_key) {
			$api_credentials = CASHSystem::getAPICredentials();
			$list_id = "100";
			// TODO: fix this crap
			// faking the URL to a valid 200 response for now, but need to update the test installer so it's...you know...less fake
			$webhook_api_url = 'http://dev.cashmusic.org/?' . $list_id . '/api_key/' . $api_credentials['api_key'];
			
			$mc = new MailchimpSeed($this->cash_user_id, $this->mailchimp_settings_id);
			$rc = $mc->listWebhookAdd($webhook_api_url);
			$this->assertTrue($rc);

			$rc = $mc->listWebhookDel($webhook_api_url);
			$this->assertTrue($rc);
		} else {
			fwrite(STDERR,"Mailchimp api key not found, skipping mailchimp tests\n");
			return;
		}
	}
}
