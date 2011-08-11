<?php

require_once('tests/php/base.php');

class CashDataTests extends UnitTestCase {

    function testCASHSettings(){
        $settings = new CASHSettings();
        $this->assertIsa($settings, 'CASHSettings');
    }

}
?>
