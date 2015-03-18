<?php
require_once(dirname(__FILE__) . '/base.php');

$test_seeds = true;
if (defined('STDIN') && count($argv) > 1) {
	if ($argv[1] == 'noseeds') {
		$test_seeds = false;
	}
}

$test = new TestSuite('All tests');
// All tests should be of the form NNN_description.php
// Notably, this excludes all.php and base.php, which are special
$test_files = glob(dirname(__FILE__) . "/*_*.php");
foreach ($test_files as $file) {
	$go = true;
	if (strpos($file,'Seed') && !$test_seeds) {
		$go = false;
	} 
	if ($go) {
		$test->addFile($file);
	}
}

if (TextReporter::inCli()) {
	echo "\n\n";
    $code = $test->run(new TextReporter()) ? 0 : 1;
    if ($code == 0) {
        echo("\nResult: PASS\n");
    } else {
        echo("\nResult: FAIL\n");
    }
    exit($code);
}

?>

