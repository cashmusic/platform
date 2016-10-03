<?php

namespace PayPal\Test\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Validation\ArgumentValidator;
use PayPal\Api\Template;
use PayPal\Rest\ApiContext;
use PayPal\Api\Templates;

/**
 * Class Templates
 *
 * @package PayPal\Test\Api
 */
class TemplatesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Templates
     * @return string
     */
    public static function getJson()
    {
        return '{"addresses":' .AddressTest::getJson() . ',"emails":"TestSample","phones":' .PhoneTest::getJson() . ',"templates":' .TemplateTest::getJson() . ',"links":' .LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Templates
     */
    public static function getObject()
    {
        return new Templates(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Templates
     */
    public function testSerializationDeserialization()
    {
        $obj = new Templates(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getAddresses());
        $this->assertNotNull($obj->getEmails());
        $this->assertNotNull($obj->getPhones());
        $this->assertNotNull($obj->getTemplates());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Templates $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getAddresses(), AddressTest::getObject());
        $this->assertEquals($obj->getEmails(), "TestSample");
        $this->assertEquals($obj->getPhones(), PhoneTest::getObject());
        $this->assertEquals($obj->getTemplates(), TemplateTest::getObject());
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @dataProvider mockProvider
     * @param Templates $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    TemplateTest::getJson()
            ));

        $result = $obj->get("templateId", $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param Templates $obj
     */
    public function testGetAll($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    TemplatesTest::getJson()
            ));
        $params = array();

        $result = $obj->getAll($params, $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }

    public function mockProvider()
    {
        $obj = self::getObject();
        $mockApiContext = $this->getMockBuilder('ApiContext')
                    ->disableOriginalConstructor()
                    ->getMock();
        return array(
            array($obj, $mockApiContext),
            array($obj, null)
        );
    }
}
