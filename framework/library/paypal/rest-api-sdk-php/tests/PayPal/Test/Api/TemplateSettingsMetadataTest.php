<?php

namespace PayPal\Test\Api;

use PayPal\Common\PayPalModel;
use PayPal\Api\TemplateSettingsMetadata;

/**
 * Class TemplateSettingsMetadata
 *
 * @package PayPal\Test\Api
 */
class TemplateSettingsMetadataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object TemplateSettingsMetadata
     * @return string
     */
    public static function getJson()
    {
        return '{"hidden":true}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return TemplateSettingsMetadata
     */
    public static function getObject()
    {
        return new TemplateSettingsMetadata(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return TemplateSettingsMetadata
     */
    public function testSerializationDeserialization()
    {
        $obj = new TemplateSettingsMetadata(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getHidden());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param TemplateSettingsMetadata $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getHidden(), true);
    }
}
