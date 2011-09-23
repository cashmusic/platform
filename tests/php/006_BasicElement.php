<?php

require_once('tests/php/base.php');
require_once('framework/php/classes/elements/TourDates.php');

class ElementTests extends UnitTestCase {
	function testTourDates(){
		$e = new TourDates('blarg',1);
		$this->assertIsa($e, 'TourDates');
	}
}
?>
