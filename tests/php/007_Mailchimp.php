<?php

require_once('tests/php/base.php');

class MailchimpTests extends UnitTestCase {
	function testMailchimpSeed(){
		$time = time();
		$api_key = getenv("MAILCHIMP_API_KEY");
		// an already-created list for testing
		$test_id = "b607c6d911";
		if($api_key) {
			// Not optimal
			$mc = new MailchimpSeed(false, false, $api_key);
			$this->assertIsa($mc, 'MailchimpSeed');
			$this->assertTrue($mc->url);
			$this->assertTrue($mc->lists());
			$webhooks = $mc->listWebhooks($test_id);
			$this->assertTrue(isset($webhooks));
			$members = $mc->listMembers($test_id);
			$this->assertTrue($members);
			$total1 = $members['total'];
			$this->assertTrue($total1);
			$this->assertTrue($members['data'][0]['email'] == 'duke@leto.net');
			$test_email = "duke$time@cashmusic.org";

			$rc = $mc->listSubscribe($test_id, $test_email, null, null, $optin=false);
			$this->assertTrue($rc);
			if (!$rc) {
				fwrite(STDERR,"Failed to add $test_email to list $test_id");
				exit(1);
			}
			$members2 = $mc->listMembers($test_id);
			$this->assertTrue($members2);
			$this->assertTrue($members2['total'] > $total1 );

			$rc = $mc->listUnsubscribe($test_id, $test_email);
			$this->assertTrue($rc);
			if (!$rc) {
				fwrite(STDERR,"Failed to remove $test_email from list $test_id");
				exit(1);
			}

			$members3 = $mc->listMembers($test_id);
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
			$mc = new MailchimpSeed(false, false, $api_key);
			$rc = $mc->listWebhookAdd($test_id, 'http://cashmusic.com/api/not/yet');
			$this->assertTrue($rc);

			$rc = $mc->listWebhookDel($test_id, 'http://cashmusic.com/api/not/yet');
			$this->assertTrue($rc);
		} else {
			fwrite(STDERR,"Mailchimp api key not found, skipping mailchimp tests\n");
			return;
		}
	}
}
