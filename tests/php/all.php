<?php
require_once('tests/lib/simpletest/autorun.php');

class AllTests extends TestSuite {
    function AllTests() {
        $this->TestSuite('All tests');
        // this sucks. We should auto-add anything in tests/php/*.php
        $this->addFile('tests/php/001_BasicTests.php');
        $this->addFile('tests/php/002_CashSettings.php');
        // $this->addFile('tests/php/003_CASHDBA.php');

        // Since test failures stop the test suite, if we get here, all
        // tests passed. This lets Jitterbug know what is up.
        print "Result: PASS\n";
    }
}
?>

