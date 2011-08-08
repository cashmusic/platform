<?php

require_once('tests/lib/simpletest/autorun.php');

class SyntaxTests extends UnitTestCase {
    public function testFrameworkSyntax() {
        $test_files = glob("{framework/php/cashmusic.php,framework/php/*/*.php,framework/php/classes/*/*.php}", GLOB_BRACE);
        foreach ($test_files as $file){
            system("php -l $file", $code);
            $this->assertTrue($code == 0);
        }
    }
    public function testInstallerSyntax() {
        $test_files = glob("installers/php/*.php");
        foreach ($test_files as $file){
            system("php -l $file", $code);
            $this->assertTrue($code == 0);
        }
    }
    public function testInterfaceSyntax() {
        $test_files = glob("interfaces/php/*/*.php");
        foreach ($test_files as $file){
            system("php -l $file", $code);
            $this->assertTrue($code == 0);
        }
    }
}
?>
