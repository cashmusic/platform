<?php
require_once('tests/php/base.php');

class AllTests extends TestSuite {
    function AllTests() {
        $this->TestSuite('All tests');
        // All tests should be of the form NNN_description.php
        // Notably, this excludes all.php and base.php, which are special
        $test_files = glob("tests/php/*_*.php");
        foreach ($test_files as $test) {
            $this->addFile($test);
        }
        // Since test failures stop the test suite, if we get here, all
        // tests passed. This lets Jitterbug know what is up.
        print "Result: PASS\n";
    }
}
?>

