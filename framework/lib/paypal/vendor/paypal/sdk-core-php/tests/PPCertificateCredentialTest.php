<?php
use PayPal\Auth\PPCertificateCredential;
use PayPal\Exception\PPMissingCredentialException;
/**
 * Test class for PPCertificateCredential.
 *
 */
class PPCertificateCredentialTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var PPCertificateCredential
	 */
	protected $credential;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->credential = new PPCertificateCredential("platfo_1255077030_biz_api1.gmail.com", "1255077037", "cacert.pem");
		$this->credential->setApplicationId('APP-80W284485P519543T');
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}

	/**@test
	 */
	public function testValidateUname()
	{
		$this->setExpectedException('PayPal\Exception\PPMissingCredentialException');
		$credUname = new PPCertificateCredential("", "1255077037", "cacert.pem");
		$credUname->validate();
		$setNotExpectedException('PPMissingCredentialException');
		$credCorrect = new PPCertificateCredential("platfo_1255077030_biz_api1.gmail.com", "1255077037", "cacert.pem");
		$var = $credCorrect->validate();
		$this->assertNull($var);
	}
	/**
	 * @test
	 */
	public function testValidatePwd()
	{
		$this->setExpectedException('PayPal\Exception\PPMissingCredentialException');
		$credpwd = new PPCertificateCredential("platfo_1255077030_biz_api1.gmail.com", "", "cacert.pem");
		$credpwd->validate();

	}
	/**
	 * @test
	 */
	public function testValidateCertPath()
	{
		$this->setExpectedException('PayPal\Exception\PPMissingCredentialException');
		$credCertPath = new PPCertificateCredential("platfo_1255077030_biz_api1.gmail.com", "1255077037", "");
		$credCertPath->validate();
	}
	/**
	 * @test
	 */
	public function testGetAppId()
	{
		$credAppid = new PPCertificateCredential("platfo_1255077030_biz_api1.gmail.com", "1255077037", "cacert.pem");
		$credAppid->setApplicationId("APP-ID");
		$this->assertEquals($credAppid->getApplicationId(), "APP-ID");
	}

	/**
	 * @test
	 */
	public function testGetUserName()
	{
		$this->assertEquals('platfo_1255077030_biz_api1.gmail.com', $this->credential->getUserName());

	}

	/**
	 * @test
	 */
	public function testGetPassword()
	{
		$this->assertEquals('1255077037', $this->credential->getPassword());
	}

	/**
	 * @test
	 */
	public function testGetCertificatePath()
	{
		$this->assertStringEndsWith(dirname(__FILE__). DIRECTORY_SEPARATOR .'cacert.pem', $this->credential->getCertificatePath());
	}

	/**@test
	 */
	public function testGetApplicationId()
	{
		$this->assertEquals('APP-80W284485P519543T', $this->credential->getApplicationId());
	}
}
?>
