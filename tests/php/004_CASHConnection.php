<?php

require_once(dirname(__FILE__) . '/base.php');

class CASHConnectionTests extends UnitTestCase {

    function testCASHConnection(){
		echo "Testing CASHConnection Class\n";

        $settings = new CASHConnection();
        $this->assertIsa($settings, 'CASHConnection');
    }

}
?>
