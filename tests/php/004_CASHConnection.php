<?php

require_once('tests/php/base.php');

class CASHConnectionTests extends UnitTestCase {

    function testCASHConnection(){
        $settings = new CASHConnection();
        $this->assertIsa($settings, 'CASHConnection');
    }

}
?>
