<?php
# error reporting
# ini_set('display_errors',1);
# error_reporting(E_ALL|E_STRICT);

require_once('lib/simpletest/autorun.php');
require_once('core/php/cashmusic.php');

class BasicTests extends UnitTestCase {

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
