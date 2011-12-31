<?php

require_once('tests/php/base.php');
require_once('framework/php/elements/TourDates.php');
require_once('framework/php/elements/SocialFeeds.php');

class ElementTests extends UnitTestCase {
	function testTourDates(){
		$e = new TourDates('blarg',1);
		$this->assertIsa($e, 'TourDates');
	}
	function testSocialFeeds(){
		$e = new SocialFeeds('blarg',1);
		$this->assertIsa($e, 'SocialFeeds');
	}
}
?>
