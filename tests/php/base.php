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

// All tests should include this file, so we can modify basic test functionality
// here without needing to modify all our tests

// this includes the basic SimpleTest library
require_once('tests/lib/simpletest/unit_tester.php');
require_once('tests/lib/simpletest/reporter.php');

// this loads CASH Music DIY
require_once('framework/php/cashmusic.php');

require_once('framework/php/classes/seeds/S3Seed.php');

// this deploys to SQLite and sets up cashmusic.ini.php
system("php installers/php/test_installer.php");

?>
