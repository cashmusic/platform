<?php

require_once('tests/lib/simpletest/autorun.php');
require_once('core/php/cashmusic.php');

class CASHDBATests extends UnitTestCase {

    public function testCASHDBA(){
        $this->assertIsA(new CASHDBA, 'CASHDBA');
    }
}
?>
