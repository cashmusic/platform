<?php
global $argv, $argc;

$test_pattern = $argc > 1 ? $argv[1] : '';
if (!$test_pattern) {
	fwrite(STDERR, "Usage: $argv[0] foobar # runs test matching 'foobar'\n");
	exit(1);
}

require_once(dirname(__FILE__) . '/base.php');

$test         = new TestSuite('Run tests');
$file_pattern = "tests/php/*$test_pattern*.php";
$test_files   = glob($file_pattern);

foreach ($test_files as $file) {
	$test->addFile($file);
}

if (TextReporter::inCli()) {
	$code = $test->run(new TextReporter()) ? 0 : 1;
	if ($code == 0) {
		echo("\nResult: PASS\n");
	} else {
		echo("\nResult: FAIL\n");
	}
	exit($code);
}

?>
