<?php
use PayPal\Core\PPCredentialManager;
/**
 * Test class for PPCredentialManager.
 *
 */
class PPCredentialManagerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var PPCredentialManager
	 */
	protected $object;

	private $config = array(
			'acct1.UserName' => 		'jb-us-seller_api1.paypal.com',
			'acct1.Password' => 		'WX4WTU3S8MY44S7F',
			'acct1.Signature' => 		'AFcWxV21C7fd0v3bYYYRCpSSRl31A7yDhhsPUU2XhtMoZXsWHFxu-RWy',
			'acct1.AppId' => 			'APP-80W284485P519543T',
			'acct1.Subject' => 			'email@paypal.com',
			'acct2.UserName' => 		'certuser_biz_api1.paypal.com',
			'acct2.Password' => 		'D6JNKKULHN3G5B8A',
			'acct2.CertPath' => 		'cert_key.pem',
			'acct2.AppId' => 			'APP-80W284485P519543T',
			'acct3.ClientId' => 		'client-id',
			'acct3.ClientSecret' => 	'client-secret',
			'http.ConnectionTimeOut' => '30',
			'http.Retry' => 			'5',
			'service.RedirectURL' => 	'https://www.sandbox.paypal.com/webscr&cmd=',
			'service.DevCentralURL' => 	'https://developer.paypal.com',
			'service.EndPoint.IPN' => 	'https://www.sandbox.paypal.com/cgi-bin/webscr',
			'service.EndPoint.AdaptivePayments' => 'https://svcs.sandbox.paypal.com/',
			'service.SandboxEmailAddress' => 'platform_sdk_seller@gmail.com',
			'log.FileName' => 			'PayPal.log',
			'log.LogLevel' => 			'INFO',
			'log.LogEnabled' => 		'1',
	);
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = PPCredentialManager::getInstance($this->config);
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
	public function testGetInstance()
	{
		$instance = $this->object->getInstance($this->config);
		$this->assertTrue($instance instanceof PPCredentialManager);
	}

	/**
	 * @test
	 */
	public function testGetSpecificCredentialObject()
	{
		$cred = $this->object->getCredentialObject('jb-us-seller_api1.paypal.com');
		$this->assertNotNull($cred);
		$this->assertEquals('jb-us-seller_api1.paypal.com', $cred->getUsername());
		
		$cred = $this->object->getCredentialObject('certuser_biz_api1.paypal.com');
		$this->assertNotNull($cred);
		$this->assertEquals('certuser_biz_api1.paypal.com', $cred->getUsername());
		$this->assertStringEndsWith('cert_key.pem', $cred->getCertificatePath());		
	}
	
	/**
	 * @test
	 */
	public function testGetInvalidCredentialObject()
	{
		$this->setExpectedException('PayPal\Exception\PPInvalidCredentialException');
		$cred = $this->object->getCredentialObject('invalid_biz_api1.gmail.com');
	}
		
	/**
	 * @test
	 */
	public function testGetDefaultCredentialObject()
	{
		$cred = $this->object->getCredentialObject();
		$this->assertEquals('jb-us-seller_api1.paypal.com', $cred->getUsername());
		
		// Test to see if default account for REST credentials works
		// as expected
		$o = PPCredentialManager::getInstance(array(
			'mode' => 'sandbox',
			'acct1.ClientId' => 		'client-id',
			'acct1.ClientSecret' => 	'client-secret',
			'acct2.UserName' => 		'certuser_biz_api1.paypal.com',
			'acct2.Password' => 		'D6JNKKULHN3G5B8A',
			'acct2.CertPath' => 		'cert_key.pem',
			'acct2.AppId' => 		'APP-80W284485P519543T'
		));
		$cred = $o->getCredentialObject();
		$this->assertEquals('client-id', $cred['clientId']);
	}	
	
	/**
	 * @test
	 */
	public function testGetPlatformCredentialObject()
	{
		$cred = $this->object->getCredentialObject();
		$this->assertEquals('APP-80W284485P519543T', $cred->getApplicationId());
	}

	/**
	 * @test
	 */
	public function testGetSubjectCredentialObject() {
		$cred = $this->object->getCredentialObject('jb-us-seller_api1.paypal.com');

		$this->assertNotNull($cred);
		$this->assertNotNull($cred->getThirdPartyAuthorization());
		$this->assertEquals('PayPal\Auth\PPSubjectAuthorization', get_class($cred->getThirdPartyAuthorization()));
	}
	
	/**
	 * @test
	 */
	public function testGetRestCredentialObject() {
		$cred = $this->object->getCredentialObject('acct3');
	
		$this->assertNotNull($cred);

		$this->assertArrayHasKey('clientId', $cred);
		$this->assertEquals($this->config['acct3.ClientId'], $cred['clientId']);

		$this->assertArrayHasKey('clientSecret', $cred);
		$this->assertEquals($this->config['acct3.ClientSecret'], $cred['clientSecret']);
	}
	
	/**
	 * @test
	 */
	public function testInvalidConfiguration() {
		$this->setExpectedException('PayPal\Exception\PPMissingCredentialException');
		$o = PPCredentialManager::getInstance(array('mode' => 'sandbox'));
	}
}
?>
