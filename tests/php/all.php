<?php
//require_once('tests/php/base.php');
require_once('tests/lib/simpletest/unit_tester.php');

$test = new TestSuite('All tests');
// All tests should be of the form NNN_description.php
// Notably, this excludes all.php and base.php, which are special
$test_files = glob("tests/php/*_*.php");
foreach ($test_files as $file) {
    $test->addFile($file);
}
if (TextReporter::inCli()) {
    exit ($test->run(new TextReporter()) ? 0 : 1);
}

?>

