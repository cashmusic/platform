<?php
require_once('tests/lib/simpletest/autorun.php');
define("PHPCOVERAGE_HOME", "tests/lib/spikephpcoverage-0.8.2/src");
require_once PHPCOVERAGE_HOME . "/CoverageRecorder.php";
require_once PHPCOVERAGE_HOME . "/reporter/HtmlCoverageReporter.php";


class AllTests extends TestSuite {
    function AllTests() {

        $reporter = new HtmlCoverageReporter("CASHMusic Code Coverage", "", "report");
        $includePaths = array("core","installers","interface");
        $excludePaths = array("tests");
        $cov = new CoverageRecorder($includePaths, $excludePaths, $reporter);
        $cov->startInstrumentation();


        $this->TestSuite('All tests');
        // this sucks. We should auto-add anything in tests/php/*.php
        $this->addFile('tests/php/001_BasicTests.php');
        $this->addFile('tests/php/002_CashSettings.php');
        // $this->addFile('tests/php/003_CASHDBA.php');

        // Since test failures stop the test suite, if we get here, all
        // tests passed. This lets Jitterbug know what is up.
        print "Result: PASS\n";

        // this is probably not the right place for this
        $cov->stopInstrumentation();
        $cov->generateReport();
        $reporter->printTextSummary();
    }
}
?>

