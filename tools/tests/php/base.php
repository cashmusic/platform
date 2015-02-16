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
require_once(dirname(__FILE__) .'/functions.php');

// this deploys to SQLite and sets up cashmusic.ini.php
system("php " . dirname(__FILE__) . "/test-installer.php");

// All tests should include this file, so we can modify basic test functionality
// here without needing to modify all our tests

// this includes the basic SimpleTest library
require_once(dirname(__FILE__) . '/../lib/simpletest/unit_tester.php');
require_once(dirname(__FILE__) . '/../lib/simpletest/reporter.php');

// this loads the platform
require_once(dirname(__FILE__) . '/../../../framework/cashmusic.php');
?>
