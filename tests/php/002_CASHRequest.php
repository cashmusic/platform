<?php

require_once(dirname(__FILE__) . '/base.php');

class CASHRequestTests extends UnitTestCase {

	function testCASHRequest(){
		echo "Testing CASHRequest Class\n";
		
		$cr = new CASHRequest();
		$this->assertIsa($cr, 'CASHRequest', 'can create a cash request with no params');

		$cr = new CASHRequest(array());
		$this->assertIsa($cr, 'CASHRequest', 'can create a cash request with empty params');

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
		$value1 = $cr->sessionGetLastResponse();
		// TODO: deeper testing of response
		$this->assertTrue($value1);

		$value = $cr->sessionClearLastResponse();
		$this->assertTrue($value);

		$value2 = $cr->sessionGetLastResponse();
		$this->assertNotEqual($value1, $value2);

		// test script-scope sesstion values:
		$value = $cr->sessionGet('foobar', 'script');
		$this->assertFalse($value);

		$cr->sessionSet('foobar', 'baz', 'script');
		$value = $cr->sessionGet('foobar', 'script');
		$this->assertEqual($value, 'baz');

		$cr->sessionClear('foobar', 'script');
		$value = $cr->sessionGet('foobar', 'script');
		$this->assertFalse($value);

		$cr->sessionSet('foobar', 'baz', 'script');
		$cr->sessionClearAll();
		$value = $cr->sessionGet('foobar', 'script');
		$this->assertFalse($value);

		// test persistent-scope sesstion values:
		$value = $cr->sessionGet('foobar');
		$this->assertFalse($value);

		$cr->sessionSet('foobar', 'baz');
		$value = $cr->sessionGet('foobar');
		$this->assertFalse($value); // fail without startSession()

		CASHSystem::startSession();
		$cr->sessionSet('foobar', 'baz');
		$value = $cr->sessionGet('foobar');
		$this->assertEqual($value, 'baz');

		$cr->sessionClear('foobar');
		$value = $cr->sessionGet('foobar');
		$this->assertFalse($value);

		$cr->sessionSet('foobar', 'baz');
		$cr->sessionClearAll();
		$value = $cr->sessionGet('foobar');
		$this->assertFalse($value);
	}

}
?>
