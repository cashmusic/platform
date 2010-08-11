<?php
/*
DB SETTINGS:
*/
$hostname = ":/Applications/MAMP/tmp/mysql/mysql.sock";
$username = "root";
$password = "root";
$database = "s3_activity";
// connect
$dblink = mysql_connect($hostname,$username,$password) or die("Unable to connect to database");
mysql_select_db($database, $dblink) or die("Unable to select database");

/*
APP SETTINGS:
*/
$php_location = '/usr/local/php5/bin/php';
$application_location = dirname(__FILE__);
$application_log = realpath(dirname(__FILE__).'/../logs/s3analysis_completion_log');
$unprocessed_location = realpath(dirname(__FILE__).'/../logs/unprocessed');
$processed_location = realpath(dirname(__FILE__).'/../logs/processed');
?>
