<?php

namespace PayPal\Test\Api;

use PayPal\Common\PayPalModel;
use PayPal\Api\Template;

/**
 * Class Template
 *
 * @package PayPal\Test\Api
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object Template
     * @return string
     */
    public static function getJson()
    {
        return '{"template_id":"TestSample","name":"TestSample","default":true,"template_data":' .TemplateDataTest::getJson() . ',"settings":' .TemplateSettingsTest::getJson() . ',"unit_of_measure":"TestSample","custom":true}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return Template
     */
    public static function getObject()
    {
        return new Template(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return Template
     */
    public function testSerializationDeserialization()
    {
        $obj = new Template(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getTemplateId());
        $this->assertNotNull($obj->getName());
        $this->assertNotNull($obj->getDefault());
        $this->assertNotNull($obj->getTemplateData());
        $this->assertNotNull($obj->getSettings());
        $this->assertNotNull($obj->getUnitOfMeasure());
        $this->assertNotNull($obj->getCustom());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param Template $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getTemplateId(), "TestSample");
        $this->assertEquals($obj->getName(), "TestSample");
        $this->assertEquals($obj->getDefault(), true);
        $this->assertEquals($obj->getTemplateData(), TemplateDataTest::getObject());
        $this->assertEquals($obj->getSettings(), TemplateSettingsTest::getObject());
        $this->assertEquals($obj->getUnitOfMeasure(), "TestSample");
        $this->assertEquals($obj->getCustom(), true);
    }
}
