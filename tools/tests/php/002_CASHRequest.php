<?php

require_once(dirname(__FILE__) . '/base.php');

class CASHRequestTests extends UnitTestCase {

	function testCASHRequest(){
		echo "Testing CASHRequest Class\n";

		$request = new CASHRequest();
		$this->assertIsa($request, 'CASHRequest', 'can create a cash request with no params');

		$request = new CASHRequest(array());
		$this->assertIsa($request, 'CASHRequest', 'can create a cash request with empty params');

		$this->assertIsa($request, 'CASHRequest');
		$request = new CASHRequest(array(
			'cash_request_type' => 'system',
			'cash_action'       => 'totallyfake',
			'id'                => 42,
		));
		$this->assertIsa($request, 'CASHRequest');
		$this->assertTrue(isset($request->response['payload']));
		$this->assertEqual($request->response['status_code'], '404');
		$this->assertEqual($request->response['status_uid'], 'system_totallyfake_404');
		$this->assertEqual($request->response['status_message'], 'Not Found');
		$this->assertEqual($request->response['contextual_message'], 'unknown action');
		$this->assertEqual($request->response['request_type'], 'system');
		$this->assertEqual($request->response['action'], 'totallyfake');

		$value1 = $request->sessionGetLastResponse();
		// TODO: deeper testing of response
		$this->assertTrue($value1);

		$value = $request->sessionClearLastResponse();
		$this->assertTrue($value);

		$value2 = $request->sessionGetLastResponse();
		$this->assertNotEqual($value1, $value2);
	}

}
?>
