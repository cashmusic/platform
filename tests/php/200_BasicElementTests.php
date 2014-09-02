<?php

require_once(dirname(__FILE__) . '/base.php');
require_once('framework/elements/TourDates.php');
require_once('framework/elements/SocialFeeds.php');

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
