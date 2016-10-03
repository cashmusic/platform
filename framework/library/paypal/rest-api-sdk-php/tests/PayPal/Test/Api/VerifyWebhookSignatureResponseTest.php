<?php

namespace PayPal\Test\Api;

use PayPal\Common\PayPalModel;
use PayPal\Api\VerifyWebhookSignatureResponse;

/**
 * Class VerifyWebhookSignatureResponse
 *
 * @package PayPal\Test\Api
 */
class VerifyWebhookSignatureResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object VerifyWebhookSignatureResponse
     * @return string
     */
    public static function getJson()
    {
        return '{"verification_status":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return VerifyWebhookSignatureResponse
     */
    public static function getObject()
    {
        return new VerifyWebhookSignatureResponse(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return VerifyWebhookSignatureResponse
     */
    public function testSerializationDeserialization()
    {
        $obj = new VerifyWebhookSignatureResponse(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getVerificationStatus());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param VerifyWebhookSignatureResponse $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getVerificationStatus(), "TestSample");
    }

}
