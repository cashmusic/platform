<?php

require_once('tests/lib/simpletest/autorun.php');

class SyntaxTests extends UnitTestCase {
    public function testSyntax() {
        system("php -l installers/php/install.php", $code);
        $this->assertTrue($code == 0);
    }
}
?>
