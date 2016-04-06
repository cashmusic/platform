<?php

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\Call;

class CallTest  {

	public function testExecuteWithExplicitCredentials() {
		$cred = new OAuthTokenCredential(\Constants::CLIENT_ID, \Constants::CLIENT_SECRET);
		$data = '"request":"test message"';

		$call = new Call();
		$ret = $call->execute('/v1/payments/echo', "POST", $data, $cred);
		$this->assertEquals($data, $ret);
	}

	public function testExecuteWithInvalidCredentials() {

		$cred = new OAuthTokenCredential('test', 'dummy');
		$data = '"request":"test message"';

		$call = new Call();
		$this->setExpectedException('\PPConnectionException');
		$ret = $call->execute('/v1/payments/echo', "POST", $data, $cred);

	}


	public function testExecuteWithDefaultCredentials() {

		$data = '"request":"test message"';

		$call = new Call();
		$ret = $call->execute('/v1/payments/echo', "POST", $data);
		$this->assertEquals($data, $ret);
	}
}
