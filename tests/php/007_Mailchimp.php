<?php

require_once('tests/php/base.php');

class MailchimpTests extends UnitTestCase {
	function testMailchimpSeed(){
		$time = time();
		$api_key = getenv("MAILCHIMP_API_KEY");
		// an already-created list for testing
		$test_id = "b607c6d911";
		if($api_key) {
			$c = new CASHSettings();
			$settings_id = $c->setSettings('MailChimp', 'com.mailchimp',
				array( "key" => $api_key, "list" => $test_id ) );

			$mc = new MailchimpSeed(false, $settings_id);
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
		// an already-created list for testing
		$test_id = "b607c6d911";
		$time = time();
		$api_key = getenv("MAILCHIMP_API_KEY");
		if($api_key) {
			$api_credentials = CASHSystem::getAPICredentials();
			$list_id = "100";
			// TODO: fix this crap
			//$webhook_api_url = CASH_API_URL . 'people/processwebhook/origin/com.mailchimp/list_id/' . $list_id . '/api_key/' . $api_credentials['api_key'];
			$webhook_api_url = 'http://cashmusic.org/people/processwebhook/origin/com.mailchimp/list_id/' . $list_id . '/api_key/' . $api_credentials['api_key'];

			$mc = new MailchimpSeed(false, false, $api_key);
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
