<?php
require_once('tests/php/base.php');
require_once('framework/php/classes/plants/PeoplePlant.php');

class PeoplePlantTests extends UnitTestCase {
	function testPeoplePlant(){
		$r = array();
		$p = new PeoplePlant('people', $r);
		$this->assertIsa($p, 'PeoplePlant');

		$p->doListSync(1);
		$this->assertTrue(1, 'Called doListSync');

		$api_key = getenv("MAILCHIMP_API_KEY");
		// an already-created list for testing
		$test_id = "b607c6d911";
		if($api_key) {
			// add syncing from a local list to $test_id mailchimp list
			// editList, then verify it worked
			// deleteList, then verify local list is removed and remote list is unchanged
			$c = new CASHSettings();
			$settings_id = $c->setSettings('MailChimp', 'com.mailchimp',
				array( "key", $api_key, "list", $test_id) );
		}
	}
}

?>
