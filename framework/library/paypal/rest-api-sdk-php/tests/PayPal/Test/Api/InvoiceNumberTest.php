<?php

namespace PayPal\Test\Api;

use PayPal\Api\InvoiceNumber;

/**
 * Class Cost
 *
 * @package PayPal\Test\Api
 */
class InvoiceNumberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Cost
     * @return string
     */
    public static function getJson()
    {
        return '{"number":"1234"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return InvoiceNumber
     */
    public static function getObject()
    {
        return new InvoiceNumber(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return InvoiceNumber
     */
    public function testSerializationDeserialization()
    {
        $obj = new InvoiceNumber(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getNumber());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param InvoiceNumber $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getNumber(), "1234");
    }
}
