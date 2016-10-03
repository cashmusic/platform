<?php

namespace PayPal\Test\Api;

use PayPal\Common\PayPalModel;
use PayPal\Api\FileAttachment;

/**
 * Class FileAttachment
 *
 * @package PayPal\Test\Api
 */
class FileAttachmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object FileAttachment
     * @return string
     */
    public static function getJson()
    {
        return '{"name":"TestSample","url":"http://www.google.com"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return FileAttachment
     */
    public static function getObject()
    {
        return new FileAttachment(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return FileAttachment
     */
    public function testSerializationDeserialization()
    {
        $obj = new FileAttachment(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getName());
        $this->assertNotNull($obj->getUrl());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param FileAttachment $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getName(), "TestSample");
        $this->assertEquals($obj->getUrl(), "http://www.google.com");
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Url is not a fully qualified URL
     */
    public function testUrlValidationForUrl()
    {
        $obj = new FileAttachment();
        $obj->setUrl(null);
    }
}
