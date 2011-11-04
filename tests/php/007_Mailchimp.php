<?php

require_once('tests/php/base.php');

class MailchimpTests extends UnitTestCase {
	function testMailchimpSeed(){
		$time = time();
		$api_key = getenv("MAILCHIMP_API_KEY");
		if($api_key) {
			$mc = new MailchimpSeed($api_key);
			$this->assertIsa($mc, 'MailchimpSeed');
			$this->assertTrue($mc->url);
			$this->assertTrue($mc->lists());
			// an already-created list for testing
			$test_id = "b607c6d911";
			$webhooks = $mc->listWebhooks($test_id);
			$this->assertTrue(isset($webhooks));
			$members = $mc->listMembers($test_id);
			$this->assertTrue($members);
			$total1 = $members['total'];
			$this->assertTrue($total1);
			$this->assertTrue($members['data'][0]['email'] == 'duke@leto.net');
			$test_email = "duke$time@cashmusic.org";

			$rc = $mc->listSubscribe($test_id, $test_email, null, null, $optin=false);
			if (!$rc) {
				fwrite(STDERR,"Failed to add email to list $test_id");
				exit(1);
			}
			$members2 = $mc->listMembers($test_id);
			$this->assertTrue($members2);
			$this->assertTrue($members2['total'] > $total1 );

			$rc = $mc->listUnsubscribe($test_id, $test_email);
			$this->assertTrue($rc);

			$members3 = $mc->listMembers($test_id);
			$this->assertTrue($members3);
			$this->assertTrue($members3['total'] == $total1 );
		} else {
			fwrite(STDERR,"Mailchimp api key not found, skipping mailchimp tests\n");
			return;
		}
	}
}
