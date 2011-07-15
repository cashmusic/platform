<?php
require_once('tests/lib/simpletest/autorun.php');

class AllTests extends TestSuite {
    function AllTests() {
        $this->TestSuite('All tests');
        $this->addFile('tests/php/001_BasicTests.php');
        $this->addFile('tests/php/002_CashData.php');
    }
}
?>

