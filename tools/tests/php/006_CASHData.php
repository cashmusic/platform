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
		echo 'Testing sessions: ' . json_encode($session) . "\n";
		$request->sessionSet('foobar', 'baz');
		$value = $request->sessionGet('foobar');
		$this->assertEqual($value, 'baz');

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
