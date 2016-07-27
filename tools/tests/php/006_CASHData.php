<?php

require_once(dirname(__FILE__) . '/base.php');

class CASHDataTests extends UnitTestCase {

	function testWhatever() {
		$request = new CASHRequest();

		// test script-scope sesstion values:
		$value = $request->sessionGet('foobar', 'script');
		$this->assertFalse($value);

		$request->sessionSet('foobar', 'baz', 'script');
		$value = $request->sessionGet('foobar', 'script');
		$this->assertEqual($value, 'baz');

		$request->sessionClear('foobar', 'script');
		$value = $request->sessionGet('foobar', 'script');
		$this->assertFalse($value);

		$request->sessionSet('foobar', 'baz', 'script');
		$request->sessionClearAll();
		$value = $request->sessionGet('foobar', 'script');
		$this->assertFalse($value);

		// test persistent-scope sesstion values:
		$value = $request->sessionGet('foobar');
		$this->assertFalse($value);

		$request->sessionSet('foobar', 'baz');
		$value = $request->sessionGet('foobar');
		$this->assertFalse($value); // fail without startSession()

		$session = CASHSystem::startSession();
		$this->assertTrue($session);
		echo 'Testing main session: ' . json_encode($session) . "\n";
		$request->sessionSet('foobar', 'baz');
		$this->assertEqual($request->sessionGet('foobar'), 'baz');
		$full_session_id = $session['id'];

		// test JS (no cookie) sessions
		$js_session_id = false;
		$GLOBALS['cashmusic_script_store'] = array(); // shenanigans: manually kill the current session

		$session_request = new CASHRequest(
			 array(
				  'cash_request_type' => 'system',
				  'cash_action' => 'startjssession'
			 )
		);
		$this->assertTrue($session_request->response['payload']);
		if ($session_request->response['payload']) {
			echo 'Testing JS session: ' . $session_request->response['payload'] . "\n";
			$s = json_decode($session_request->response['payload'],true);
			$js_session_id = $s['id'];
		}
		$this->assertTrue($js_session_id);
		if ($js_session_id) {
			$request->sessionSet('what', 'this');
			$this->assertEqual($request->sessionGet('what'), 'this');
			$this->assertNotEqual($request->sessionGet('foobar'), 'baz');
		}

		echo "Testing session coexistence\n";

		// test that previous session values are valid
		// and that we still have JS session values working side by side
		CASHSystem::startSession($full_session_id);
		$value = $request->sessionGet('foobar');
		$this->assertEqual($request->sessionGet('foobar'), 'baz');
		$this->assertNotEqual($request->sessionGet('what'), 'this');

		CASHSystem::startSession($js_session_id);
		$this->assertEqual($request->sessionGet('what'), 'this');
		$this->assertNotEqual($request->sessionGet('foobar'), 'baz');

		CASHSystem::startSession($full_session_id);

		// test clearing sessions
		$request->sessionClear('foobar');
		$value = $request->sessionGet('foobar');
		$this->assertFalse($value);

		$request->sessionSet('foobar', 'baz');
		$request->sessionClearAll();
		$value = $request->sessionGet('foobar');
		$this->assertFalse($value);
	}
}
?>
