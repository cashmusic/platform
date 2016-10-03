<?php

namespace PayPal\Test\Api;

use PayPal\Common\PayPalModel;
use PayPal\Api\PaymentSummary;

/**
 * Class PaymentSummary
 *
 * @package PayPal\Test\Api
 */
class PaymentSummaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PaymentSummary
     * @return string
     */
    public static function getJson()
    {
        return '{"paypal":' .CurrencyTest::getJson() . ',"other":' .CurrencyTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PaymentSummary
     */
    public static function getObject()
    {
        return new PaymentSummary(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PaymentSummary
     */
    public function testSerializationDeserialization()
    {
        $obj = new PaymentSummary(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getPaypal());
        $this->assertNotNull($obj->getOther());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PaymentSummary $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getPaypal(), CurrencyTest::getObject());
        $this->assertEquals($obj->getOther(), CurrencyTest::getObject());
    }
}
