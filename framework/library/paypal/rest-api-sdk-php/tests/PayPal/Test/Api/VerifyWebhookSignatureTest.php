<?php

namespace PayPal\Test\Api;

use PayPal\Common\PayPalResourceModel;
use PayPal\Validation\ArgumentValidator;
use PayPal\Api\VerifyWebhookSignatureResponse;
use PayPal\Rest\ApiContext;
use PayPal\Api\VerifyWebhookSignature;

/**
 * Class VerifyWebhookSignature
 *
 * @package PayPal\Test\Api
 */
class VerifyWebhookSignatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object VerifyWebhookSignature
     * @return string
     */
    public static function getJson()
    {
        return '{"auth_algo":"TestSample","cert_url":"http://www.google.com","transmission_id":"TestSample","transmission_sig":"TestSample","transmission_time":"TestSample","webhook_id":"TestSample","webhook_event":' .WebhookEventTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return VerifyWebhookSignature
     */
    public static function getObject()
    {
        return new VerifyWebhookSignature(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return VerifyWebhookSignature
     */
    public function testSerializationDeserialization()
    {
        $obj = new VerifyWebhookSignature(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getAuthAlgo());
        $this->assertNotNull($obj->getCertUrl());
        $this->assertNotNull($obj->getTransmissionId());
        $this->assertNotNull($obj->getTransmissionSig());
        $this->assertNotNull($obj->getTransmissionTime());
        $this->assertNotNull($obj->getWebhookId());
        $this->assertNotNull($obj->getWebhookEvent());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param VerifyWebhookSignature $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getAuthAlgo(), "TestSample");
        $this->assertEquals($obj->getCertUrl(), "http://www.google.com");
        $this->assertEquals($obj->getTransmissionId(), "TestSample");
        $this->assertEquals($obj->getTransmissionSig(), "TestSample");
        $this->assertEquals($obj->getTransmissionTime(), "TestSample");
        $this->assertEquals($obj->getWebhookId(), "TestSample");
        $this->assertEquals($obj->getWebhookEvent(), WebhookEventTest::getObject());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage CertUrl is not a fully qualified URL
     */
    public function testUrlValidationForCertUrl()
    {
        $obj = new VerifyWebhookSignature();
        $obj->setCertUrl(null);
    }
    /**
     * @dataProvider mockProvider
     * @param VerifyWebhookSignature $obj
     */
    public function testPost($obj, $mockApiContext)
    {
        $mockPPRestCall = $this->getMockBuilder('\PayPal\Transport\PayPalRestCall')
            ->disableOriginalConstructor()
            ->getMock();

        $mockPPRestCall->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(
                    VerifyWebhookSignatureResponseTest::getJson()
            ));

        $result = $obj->post($mockApiContext, $mockPPRestCall);
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
