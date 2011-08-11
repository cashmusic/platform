<?php

require_once('tests/lib/simpletest/autorun.php');
require_once('framework/php/cashmusic.php');

class CashDataTests extends UnitTestCase {

    function testCASHSettings(){
        $settings = new CASHSettings();
        $this->assertIsa($settings, 'CASHSettings');
    }

}
?>
