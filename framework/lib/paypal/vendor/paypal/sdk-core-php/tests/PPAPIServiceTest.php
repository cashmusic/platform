<?php

require_once 'Mocks.php';

use PayPal\Core\PPAPIService;
use PayPal\Core\PPRequest;
use PayPal\Common\PPApiContext;


/**
 * Test class for PPAPIService.
 *
 */
class PPAPIServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PPAPIService
     */
    protected $object;

    private $config = array(
    		'acct1.UserName' => 'jb-us-seller_api1.paypal.com'	,
    		'acct1.Password' => 'WX4WTU3S8MY44S7F'	,
    		'acct1.Signature' => 	'AFcWxV21C7fd0v3bYYYRCpSSRl31A7yDhhsPUU2XhtMoZXsWHFxu-RWy'	,
    		'acct1.AppId' => 	'APP-80W284485P519543T'	,
    		'acct2.UserName' => 	'certuser_biz_api1.paypal.com'	,
    		'acct2.Password' => 	'D6JNKKULHN3G5B8A'	,
    		'acct2.CertPath' => 	'cert_key.pem'	,
    		'acct2.AppId' => 	'APP-80W284485P519543T'	,
    		'http.ConnectionTimeOut' => 	'30'	,
    		'http.Retry' => 	'5'	,
    		'service.RedirectURL' => 	'https://www.sandbox.paypal.com/webscr&cmd='	,
    		'service.DevCentralURL' => 'https://developer.paypal.com'	,
    		'service.EndPoint.IPN' => 'https://www.sandbox.paypal.com/cgi-bin/webscr'	,
    		'service.EndPoint.Invoice' => 'https://svcs.sandbox.paypal.com/'	,
    		'service.SandboxEmailAddress' => 'platform_sdk_seller@gmail.com',
    		'log.FileName' => 'PayPal.log'	,
    		'log.LogLevel' => 	'INFO'	,
    		'log.LogEnabled' => 	'1'	,


    );

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PPAPIService(null, 'Invoice', 'NV', new PPApiContext($this->config), array());
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
    public function testSetServiceName()
    {
    	$this->assertEquals('Invoice', $this->object->serviceName);
    	$this->object->setServiceName('AdaptiveAccounts');
        $this->assertEquals('AdaptiveAccounts', $this->object->serviceName);
    }

    /**
     * @test
     */
    public function testMakeRequestWithoutHandlers() {
    	$this->object->setServiceName('Invoice');
    	$this->setExpectedException('PayPal\Exception\PPConnectionException');
	$req = new PPRequest(new MockNVPClass(), "NV");
	$this->object->makeRequest('GetInvoiceDetails', $req);
    }

    /**
     * @test
     */
    public function testMakeRequestWithHandlers() {
    	$this->object->addHandler(new MockHandler());
	$req = new PPRequest(new MockNVPClass(), "NV");
    	$ret = $this->object->makeRequest('GetInvoiceDetails', $req);

    	$this->assertArrayHasKey('response', $ret);
    	$this->assertContains("responseEnvelope.timestamp=", $ret['response']);
    }
}
