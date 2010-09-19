<?php
// include global settings
include(dirname(__FILE__).'/../php/settings.php');

exec("$php_location $application_location/parselatest.php > /dev/null &");

?>
