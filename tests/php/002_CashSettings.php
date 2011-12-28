<?php

require_once('tests/php/base.php');

class CashDataTests extends UnitTestCase {

    function testCASHConnections(){
        $settings = new CASHConnections();
        $this->assertIsa($settings, 'CASHConnections');
    }

}
?>
