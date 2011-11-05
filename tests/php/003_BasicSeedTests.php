<?php

require_once('tests/php/base.php');

class CashSeedTests extends UnitTestCase {
	function testS3Seed(){
		$settings = new S3Seed(1,1);
		$this->assertIsa($settings, 'S3Seed');
	}
	function testTwitterSeed(){
		$user_id      = 1;
		$settings_id  = 1;
		$twitter      = new TwitterSeed($user_id,$settings_id);
		$this->assertIsa($twitter, 'TwitterSeed');
	}
	function testMailchimpSeed(){
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
			$this->assertTrue($members['total']);
			$this->assertTrue($members['data'][0]['email'] == 'duke@leto.net');
		} else {
			fwrite(STDERR,"Mailchimp api key not found, skipping mailchimp tests\n");
			return;
		}
	}
}
?>
