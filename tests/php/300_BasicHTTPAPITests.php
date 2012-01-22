<?php

require_once('tests/php/base.php');

class BasicHTTPAPITests extends UnitTestCase {

	function testValidDestination() {
		$return = json_decode(CASHSystem::getURLContents(CASH_API_URL));
		
		$this->assertNotNull($return); // invalid JSON should return NULL - though this is only a basic validation
		$this->assertEqual($return->greeting,'hi.'); // test that it gave proper default content
		$this->assertWithinMargin(time(),$return->timestamp,150); // timestamp is closish to now 
	}

	function testReturnStatuses() {
		$forbidden_return = json_decode(CASHSystem::getURLContents(CASH_API_URL . 'verbose/element/getelement/321',false,true));
		$badrequest_return = json_decode(CASHSystem::getURLContents(CASH_API_URL . 'verbose/element/getmarkup/321/status_uid/whatever',false,true));
		$ok_return = json_decode(CASHSystem::getURLContents(CASH_API_URL . 'verbose/element/getmarkup/100/status_uid/whatever'));
		
		$this->assertEqual($forbidden_return->status_code,403);
		$this->assertEqual($badrequest_return->status_code,400);
		$this->assertEqual($ok_return->status_code,200);
	}

	function testValidReturn() {
		$return = json_decode(CASHSystem::getURLContents(CASH_API_URL . 'verbose/element/getmarkup/100/status_uid/whatever',false,true));
			// make sure all the bits and pieces are in place
			$this->assertTrue(isset($return->status_code));
			$this->assertTrue(isset($return->status_uid));
			$this->assertTrue(isset($return->status_message));
			$this->assertTrue(isset($return->contextual_message));
			$this->assertTrue(isset($return->request_type));
			$this->assertTrue(isset($return->action));
			$this->assertTrue(isset($return->payload));
			$this->assertTrue(isset($return->api_version));
			$this->assertTrue(isset($return->timestamp));
		
			// test types for the standardized bits, ignore the variable pieces
			$this->assertTrue(is_int($return->status_code));
			$this->assertTrue(is_string($return->status_uid));
			$this->assertTrue(is_string($return->status_message));
			$this->assertTrue(is_string($return->contextual_message));
			$this->assertTrue(is_string($return->request_type));
			$this->assertTrue(is_string($return->action));
			$this->assertTrue(is_int($return->api_version));
			$this->assertTrue(is_int($return->timestamp));
	}

}
?>
