<?php

require_once('tests/lib/simpletest/autorun.php');
require_once('core/php/cashmusic.php');

class CashDataTests extends UnitTestCase {

    function testCASHData(){
        $data = new CASHData();
    }

    function assertFileExists($filename, $message = '%s') {
        $this->assertTrue(
                file_exists($filename),
                sprintf($message, 'File [$filename] existence check'));
    }
}
?>
