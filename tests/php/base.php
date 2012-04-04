<?php
// why is it so hard to tell PHP you want to see errors?
ini_set('track_errors', 1);
error_reporting(-1); // report all errors

// The Terminator helps keep our code "strong like bull"
// by exiting with a non-sucessful exit code for any PHP error
function terminator($errno, $errstr, $errfile, $errline)
{
	fwrite(STDERR,"$errstr in $errfile line $errline");
	exit(1);
}
set_error_handler("terminator");

// Get environment variables from local file or from environment
function getTestEnv($varname) {
	if (file_exists(dirname(__FILE__) . '/__test_environment.json')) {
		$environment = json_decode(file_get_contents(dirname(__FILE__) . '/__test_environment.json'),true);
		if (isset($environment[$varname])) {
			return $environment[$varname];
		} else {
			return false;
		}
	} else {
		return getenv($varname);
	}
}

// this deploys to SQLite and sets up cashmusic.ini.php
// sleep to avoid race condition if PHP is set to handle system as sub-process
system("php installers/php/test_installer.php");
sleep(3);

// All tests should include this file, so we can modify basic test functionality
// here without needing to modify all our tests

// this includes the basic SimpleTest library
require_once('tests/lib/simpletest/unit_tester.php');
require_once('tests/lib/simpletest/reporter.php');

// this loads CASH Music DIY
require_once('framework/php/cashmusic.php');
?>
