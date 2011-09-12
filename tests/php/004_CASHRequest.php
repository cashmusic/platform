<?php

require_once('tests/php/base.php');

class CASHRequestTests extends UnitTestCase {

	function testCASHRequest(){
		// TODO: We should be operating on asset id's that actually exist in our test db
		$cr = new CASHRequest(array(
			'cash_request_type' => 'asset',
			'cash_action'       => 'unlock',
			'asset_id'          => 42,
		));
		$this->assertIsa($cr, 'CASHRequest');
		$cr = new CASHRequest(array(
			'cash_request_type' => 'asset',
			'cash_action'       => 'lock',
			'asset_id'          => 42,
		));
		$this->assertIsa($cr, 'CASHRequest');

		$value = $cr->sessionGetPersistent("foobar");
		$this->assertFalse($value);

		$cr->sessionSetPersistent("foobar", "baz");
		$value = $cr->sessionGetPersistent("foobar");
		$this->assertEqual($value, "baz");

		$cr->sessionClearPersistent("foobar");
		$value = $cr->sessionGetPersistent("foobar");
		$this->assertFalse($value);

		$cr->sessionSetPersistent("foobar", "baz");
		$cr->sessionClearAllPersistent();
		$value = $cr->sessionGetPersistent("foobar");
		$this->assertFalse($value);
	}

}
?>
