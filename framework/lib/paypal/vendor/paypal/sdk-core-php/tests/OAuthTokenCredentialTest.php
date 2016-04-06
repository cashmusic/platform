<?php


use PayPal\Auth\OAuthTokenCredential;
use PayPal\Core\PPConfigManager;
use PayPal\Exception\PPConnectionException;

class OAuthTokenCredentialTest extends PHPUnit_Framework_TestCase {

	public function testGetAccessToken() {
		$cred = new OAuthTokenCredential(Constants::CLIENT_ID, Constants::CLIENT_SECRET);
		$config = PPConfigManager::getConfigWithDefaults();

		$token = $cred->getAccessToken($config);
		$this->assertNotNull($token);

		// Check that we get the same token when issuing a new call before token expiry
		$newToken = $cred->getAccessToken($config);
		$this->assertNotNull($newToken);
		$this->assertEquals($token, $newToken);

// 		sleep(60*8);
// 		$newToken = $cred->getAccessToken();
// 		$this->assertNotNull($newToken);
// 		$this->assertNotEqual($token, $newToken);

	}

	public function testInvalidCredentials() {
		$this->setExpectedException('PayPal\Exception\PPConnectionException');
		$cred = new OAuthTokenCredential('dummy', 'secret');
		$this->assertNull($cred->getAccessToken(PPConfigManager::getConfigWithDefaults()));
	}
}
