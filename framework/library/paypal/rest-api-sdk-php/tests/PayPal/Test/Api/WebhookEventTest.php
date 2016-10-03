<?php

namespace PayPal\Test\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Validation\ArgumentValidator;
use PayPal\Api\WebhookEventList;
use PayPal\Rest\ApiContext;
use PayPal\Api\WebhookEvent;

/**
 * Class WebhookEvent
 *
 * @package PayPal\Test\Api
 */
class WebhookEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object WebhookEvent
     * @return string
     */
    public static function getJson()
    {
        return '{"id":"TestSample","create_time":"TestSample","resource_type":"TestSample","event_version":"TestSample","event_type":"TestSample","summary":"TestSample","resource":"TestSampleObject","status":"TestSample","transmissions":"TestSampleObject","links":' .LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return WebhookEvent
     */
    public static function getObject()
    {
        return new WebhookEvent(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return WebhookEvent
     */
    public function testSerializationDeserialization()
    {
        $obj = new WebhookEvent(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getId());
        $this->assertNotNull($obj->getCreateTime());
        $this->assertNotNull($obj->getResourceType());
        $this->assertNotNull($obj->getEventVersion());
        $this->assertNotNull($obj->getEventType());
        $this->assertNotNull($obj->getSummary());
        $this->assertNotNull($obj->getResource());
        $this->assertNotNull($obj->getStatus());
        $this->assertNotNull($obj->getTransmissions());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param WebhookEvent $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getId(), "TestSample");
        $this->assertEquals($obj->getCreateTime(), "TestSample");
        $this->assertEquals($obj->getResourceType(), "TestSample");
        $this->assertEquals($obj->getEventVersion(), "TestSample");
        $this->assertEquals($obj->getEventType(), "TestSample");
        $this->assertEquals($obj->getSummary(), "TestSample");
        $this->assertEquals($obj->getResource(), "TestSampleObject");
        $this->assertEquals($obj->getStatus(), "TestSample");
        $this->assertEquals($obj->getTransmissions(), "TestSampleObject");
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

    /**
     * @dataProvider mockProvider
     * @param WebhookEvent $obj
     */
    public function testGet($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    WebhookEventTest::getJson()
            ));

        $result = $obj->get("eventId", $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param WebhookEvent $obj
     */
    public function testResend($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    self::getJson()
            ));
        $eventResend = EventResendTest::getObject();

        $result = $obj->resend($eventResend, $mockApiContext, $mockPPRestCall);
        $this->assertNotNull($result);
    }
    /**
     * @dataProvider mockProvider
     * @param WebhookEvent $obj
     */
    public function testList($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    WebhookEventListTest::getJson()
            ));
        $params = array();

        $result = $obj->all($params, $mockApiContext, $mockPPRestCall);
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
