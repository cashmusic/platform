<?php
use PayPal\Auth\PPSignatureCredential;
use PayPal\Auth\PPTokenAuthorization;
use PayPal\Auth\PPSubjectAuthorization;
/**
 * Test class for PPSignatureCredential.
 *
 */
class PPSignatureCredentialTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var PPSignatureCredential
	 */
	protected $merchantCredential;

	protected $platformCredential;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->merchantCredential = new PPSignatureCredential("platfo_1255077030_biz_api1.gmail.com", "1255077037","Abg0gYcQyxQvnf2HDJkKtA-p6pqhA1k-KTYE0Gcy1diujFio4io5Vqjf");

		$this->platformCredential = new PPSignatureCredential("platfo_1255077030_biz_api1.gmail.com", "1255077037","Abg0gYcQyxQvnf2HDJkKtA-p6pqhA1k-KTYE0Gcy1diujFio4io5Vqjf");
		$this->platformCredential->setApplicationId("APP-80W284485P519543T");
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}

	/**
	 * @test
	 */
	public function testValidateUsername()
	{
		$this->setExpectedException('PayPal\Exception\PPMissingCredentialException');
		$cred = new PPSignatureCredential("", "1255077037","Abg0gYcQyxQvnf2HDJkKtA-p6pqhA1k-KTYE0Gcy1diujFio4io5Vqjf");
		$cred->validate();
	}

	/**
	 * @test
	 */
	public function testValidatepwd()
	{
		$this->setExpectedException('PayPal\Exception\PPMissingCredentialException');
		$cred = new PPSignatureCredential("platfo_1255077030_biz_api1.gmail.com", "","Abg0gYcQyxQvnf2HDJkKtA-p6pqhA1k-KTYE0Gcy1diujFio4io5Vqjf");
		$cred->validate();
	}

	/**
	 * @test
	 */
	public function testGetSignature()
	{
		$this->assertEquals('Abg0gYcQyxQvnf2HDJkKtA-p6pqhA1k-KTYE0Gcy1diujFio4io5Vqjf', $this->merchantCredential->getSignature());
	}
	/**
	 * @test
	 */
	public function testGetUserName()
	{
		$this->assertEquals('platfo_1255077030_biz_api1.gmail.com', $this->merchantCredential->getUserName());
	}
	/**
	 * @test
	 */
	public function testGetPassword()
	{
		$this->assertEquals('1255077037', $this->merchantCredential->getPassword());
	}
	/**
	 * @test
	 */
	public function testGetAppId()
	{
		$this->assertEquals('APP-80W284485P519543T', $this->platformCredential->getApplicationId());
	}
	
	public function testThirdPartyAuthorization() {
		$authorizerEmail = "merchant@domain.com";
		$thirdPartyAuth = new PPSubjectAuthorization($authorizerEmail);		
		$cred = new PPSignatureCredential("username", "pwd", "signature");
		$cred->setThirdPartyAuthorization($thirdPartyAuth);		
		$this->assertEquals($cred->getThirdPartyAuthorization()->getSubject(), $authorizerEmail);
		
		$accessToken = "atoken";
		$tokenSecret = "asecret";
		$thirdPartyAuth = new PPTokenAuthorization($accessToken, $tokenSecret);
		$cred->setThirdPartyAuthorization($thirdPartyAuth);
		$this->assertEquals($cred->getThirdPartyAuthorization()->getAccessToken(), $accessToken);
		$this->assertEquals($cred->getThirdPartyAuthorization()->getTokenSecret(), $tokenSecret);
	}

}
?>
