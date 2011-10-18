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
		if(array_key_exists('MAILCHIMP_API_KEY', $_ENV)) {
			$key = $_ENV['MAILCHIMP_API_KEY'];
			$mc = new MailchimpSeed($key);
			$this->assertIsa($mc, 'MailchimpSeed');
			$this->assertTrue($mc->url);
			$this->assertTrue($mc->lists());
			$this->assertTrue($mc->listWebhooks(42));
			$this->assertTrue($mc->listWebhooks(42));
			$this->assertTrue($mc->listMembers(1));
		} else {
			fwrite(STDERR,"Mailchimp api key not found, skipping mailchimp tests\n");
			return;
		}
	}
}
?>
