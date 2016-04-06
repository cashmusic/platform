<?php
use PayPal\Core\PPUtils;
/**
 * Test class for PPUtils.
 *
 */
class PPUtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PPUtils
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PPUtils();
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
    public function testNvpToMap()
    {
       $arr = $this->object->nvpToMap('requestEnvelope.detailLevel=ReturnAll&requestEnvelope.errorLanguage=en_US&invoice.merchantEmail=jb-us-seller1@paypal.com&invoice.payerEmail=jbui-us-personal1@paypal.com&invoice.items[0].name=product1&invoice.items[0].quantity=10.0&invoice.items[0].unitPrice=1.2&invoice.currencyCode=USD&invoice.paymentTerms=DueOnReceipt');
       $this->assertArrayHasKey('requestEnvelope.detailLevel', $arr);
       $this->assertArrayHasKey('requestEnvelope.errorLanguage', $arr);
       $this->assertEquals(is_array($arr),true);
    }

    /**
     * @test
     */
    public function testArray_match_key()
    {
	$arr = array('key1' => 'somevalue', 'key2' => 'someothervalue');
	$this->assertEquals(true, PPUtils::array_match_key($arr, "key"));
		
		$arr = array('key1' => 'somevalue', 'key2' => 'someothervalue');
		$this->assertEquals(false, PPUtils::array_match_key($arr, "prefix"));
		
		$arr = unserialize('a:10:{s:26:"responseEnvelope.timestamp";s:35:"2011-04-19T04%3A32%3A29.469-07%3A00";s:20:"responseEnvelope.ack";s:7:"Failure";s:30:"responseEnvelope.correlationId";s:13:"c2514f258ddf1";s:22:"responseEnvelope.build";s:7:"1829457";s:16:"error(0).errorId";s:6:"580027";s:15:"error(0).domain";s:8:"PLATFORM";s:17:"error(0).severity";s:5:"Error";s:17:"error(0).category";s:11:"Application";s:16:"error(0).message";s:44:"Prohibited+request+parameter%3A+businessInfo";s:21:"error(0).parameter(0)";s:12:"businessInfo";}');
		$this->assertEquals(true, PPUtils::array_match_key($arr, "error(0)."));
    }

    /**
     * @test
     */
    public function testGetLocalIPAddress()
    {
        $ip = $this->object->getLocalIPAddress();
        $this->assertEquals($ip, filter_var($ip, FILTER_VALIDATE_IP));
        
        $_SERVER['SERVER_ADDR'] = '127.0.0.1';
        $ip = $this->object->getLocalIPAddress();
        $this->assertEquals($ip, filter_var($ip, FILTER_VALIDATE_IP));
    }

    /**
     * @test
     */
    public function testValidXmlToArray() {

    	$requestPayload = '<SetExpressCheckoutResponse xmlns="urn:ebay:api:PayPalAPI"><Timestamp xmlns="urn:ebay:apis:eBLBaseComponents">2013-07-23T05:51:03Z</Timestamp><Ack xmlns="urn:ebay:apis:eBLBaseComponents"><Nested>Success</Nested></Ack><CorrelationID xmlns="urn:ebay:apis:eBLBaseComponents">1cf4475882d05</CorrelationID><Version xmlns="urn:ebay:apis:eBLBaseComponents">94.0</Version><Build xmlns="urn:ebay:apis:eBLBaseComponents">6941909</Build><Token xsi:type="ebl:ExpressCheckoutTokenType" attrib="someValue">EC-6KT84265CE1992425</Token></SetExpressCheckoutResponse>';

    	$xml = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:cc="urn:ebay:apis:CoreComponentTypes" xmlns:wsu="http://schemas.xmlsoap.org/ws/2002/07/utility" xmlns:saml="urn:oasis:names:tc:SAML:1.0:assertion" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/12/secext" xmlns:ed="urn:ebay:apis:EnhancedDataTypes" xmlns:ebl="urn:ebay:apis:eBLBaseComponents" xmlns:ns="urn:ebay:api:PayPalAPI">'
		. '<SOAP-ENV:Header><Security xmlns="http://schemas.xmlsoap.org/ws/2002/12/secext" xsi:type="wsse:SecurityType"></Security><RequesterCredentials xmlns="urn:ebay:api:PayPalAPI" xsi:type="ebl:CustomSecurityHeaderType"><Credentials xmlns="urn:ebay:apis:eBLBaseComponents" xsi:type="ebl:UserIdPasswordType"><Username xsi:type="xs:string"></Username><Password xsi:type="xs:string"></Password><Signature xsi:type="xs:string"></Signature><Subject xsi:type="xs:string"></Subject></Credentials></RequesterCredentials></SOAP-ENV:Header>'
		. '<SOAP-ENV:Body id="_0">'
		. $requestPayload
		. '</SOAP-ENV:Body></SOAP-ENV:Envelope>';

    	$ret = PPUtils::xmlToArray($xml);
    	
        $this->assertEquals("SetExpressCheckoutResponse", $ret[0]['name']);

        $ret = $ret[0]['children'];
    	$this->assertEquals(6, count($ret));
    	
	// Token node
    	$this->assertFalse(array_key_exists('children', $ret[5]));
    	$this->assertEquals("Token", $ret[5]['name']);
    	$this->assertEquals("EC-6KT84265CE1992425", $ret[5]['text']);
    	
    	$this->assertEquals(1, count($ret[5]['attributes']));
    	$k = key($ret[5]['attributes']);
    	$this->assertEquals("attrib", $k);
    	$this->assertEquals("someValue", $ret[5]['attributes'][$k]);
    	
    	// Ack Node
    	$this->assertEquals("Ack", $ret[1]['name']);
    	$this->assertEquals(1, count($ret[1]['children']));
    	$this->assertEquals("Nested", $ret[1]['children'][0]['name']);
    	$this->assertEquals("Success", $ret[1]['children'][0]['text']);

    }

    /**
	 * @test
	 */
	 function testSoapFaultXml() {
		$xml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"><soapenv:Header/><soapenv:Body> <soapenv:Fault xmlns:axis2ns237961="http://schemas.xmlsoap.org/soap/envelope/"><faultcode>axis2ns237961:Server</faultcode><faultstring>Authentication failed. API credentials are incorrect.</faultstring><detail><ns3:FaultMessage xmlns:ns3="http://svcs.paypal.com/types/common" xmlns:ns2="http://svcs.paypal.com/types/ap"><responseEnvelope><timestamp>2013-09-03T04:36:14.931-07:00</timestamp><ack>Failure</ack><correlationId>ebeb480862a99</correlationId><build>6941298</build></responseEnvelope><error><errorId>520003</errorId><domain>PLATFORM</domain><subdomain>Application</subdomain><severity>Error</severity><category>Application</category><message>Authentication failed. API credentials are incorrect.</message></error></ns3:FaultMessage></detail></soapenv:Fault></soapenv:Body></soapenv:Envelope>';

		$ret = PPUtils::xmlToArray($xml);
		$this->assertEquals("soapenv:Fault", $ret[0]['name']);
	 }

    /**
     * @test
     */
    public function testGetProperties() {
    	$o = new MockReflectionTestType();
    	$ret = PPUtils::objectProperties($o);

    	//TODO: Check count
    	$this->assertEquals(6, count($ret), "Not all properties have been read");
    	$this->assertEquals('fieldWithSpecialChar', $ret['fieldwith-specialchar']);

    }
    
    /**
     * @test
     */
    public function testGetPropertyType() {

    	$this->assertEquals('string', PPUtils::propertyType('MockReflectionTestType', 'noAnnotations'));
    	$this->assertEquals('SomeType', PPUtils::propertyType('MockReflectionTestType', 'value'));

    	$this->assertEquals(true, PPUtils::isPropertyArray('MockReflectionTestType', 'arrayMember'));
    	$this->assertEquals(false, PPUtils::isPropertyArray('MockReflectionTestType', 'value'));
    }
    
    /**
     * @test
     */
    public function testGetAttributeType() {
    	$this->assertEquals(true, PPUtils::isAttributeProperty('MockReflectionTestType', 'currencyID'));
    	$this->assertEquals(false, PPUtils::isAttributeProperty('MockReflectionTestType', 'value'));
    	$this->assertEquals(false, PPUtils::isAttributeProperty('MockReflectionTestType', 'noAnnotations'));
    }
}

/**
 * @hasAttribute
 * On requests, you must set the currencyID attribute to one of
 * the three-character currency codes for any of the supported
 * PayPal currencies. Limitations: Must not exceed $10,000 USD
 * in any currency. No currency symbol. Decimal separator must
 * be a period (.), and the thousands separator must be a comma
 * (,).
 */
class MockReflectionTestType {

	/**
	 *
	 * @access public
	 * @namespace cc
	 * @attribute
	 * @var string
	 */
	public $currencyID;

	/**
	 *
	 * @access public
	 * @namespace cc
	 * @value
	 * @var SomeType
	 */
	public $value;

	/**
	 * @name fieldWith-SpecialChar
	 * @access public
	 * @var string
	 */
	public $fieldWithSpecialChar;

	/**
	 * @access public
	 * @array
	 * @var string
	 */
	public $arrayMember;

	/**
	 *
	 */
	public $noAnnotations;



}
?>
