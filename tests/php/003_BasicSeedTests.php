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
		$settings = new MailchimpSeed('keyblarg-us2');
		$this->assertIsa($settings, 'MailchimpSeed');
		$this->assertTrue($settings->url);
		$this->assertTrue($settings->lists());
		$this->assertTrue($settings->listWebhooks(42));
	}
}
?>
