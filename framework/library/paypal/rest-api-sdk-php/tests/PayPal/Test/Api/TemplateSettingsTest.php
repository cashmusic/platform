<?php

namespace PayPal\Test\Api;

use PayPal\Common\PayPalModel;
use PayPal\Api\TemplateSettings;

/**
 * Class TemplateSettings
 *
 * @package PayPal\Test\Api
 */
class TemplateSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object TemplateSettings
     * @return string
     */
    public static function getJson()
    {
        return '{"field_name":"TestSample","display_preference":' .TemplateSettingsMetadataTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return TemplateSettings
     */
    public static function getObject()
    {
        return new TemplateSettings(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return TemplateSettings
     */
    public function testSerializationDeserialization()
    {
        $obj = new TemplateSettings(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getFieldName());
        $this->assertNotNull($obj->getDisplayPreference());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param TemplateSettings $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getFieldName(), "TestSample");
        $this->assertEquals($obj->getDisplayPreference(), TemplateSettingsMetadataTest::getObject());
    }
}
