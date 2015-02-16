<?php

require_once(dirname(__FILE__) . '/base.php');


class SyntaxTests extends UnitTestCase {
    public function checkSyntax($file) {
        $output = exec("php -l $file", $array, $code);
        $this->assertTrue($code == 0, $output);
    }

    public function testFrameworkSyntax() {
        $test_files = glob("{framework/cashmusic.php,framework/php/*/*.php,framework/php/classes/*/*.php}", GLOB_BRACE);
        foreach ($test_files as $file){
            $this->checkSyntax($file);
        }
    }
    public function testInterfaceSyntax() {
        $test_files = glob("{interfaces/*/*.php,interfaces/*/*/*.php,interfaces/*/*/*/*.php,interfaces/*/*/*/*/*.php}", GLOB_BRACE);
        foreach ($test_files as $file){
            $this->checkSyntax($file);
        }
    }
}
?>
