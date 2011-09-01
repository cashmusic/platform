<?php

require_once('tests/php/base.php');

class CashSeedTests extends UnitTestCase {

    function testS3Seed(){
        $settings = new S3Seed(1,1);
        $this->assertIsa($settings, 'S3Seed');
    }

}
?>
