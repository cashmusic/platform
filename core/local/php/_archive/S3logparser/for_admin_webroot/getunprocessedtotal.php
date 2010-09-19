<?php
// include global settings
include(dirname(__FILE__).'/../php/settings.php');

$totallogfiles = 0;
if ($handle = opendir($unprocessed_location)) {
    while (false !== ($logfile = readdir($handle))) {
        if (substr($logfile,0,1) != ".") {
            $totallogfiles = $totallogfiles+1;
        }
    }
    closedir($handle);
}

echo $totallogfiles;
?>
