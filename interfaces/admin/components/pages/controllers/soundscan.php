<?php

error_log("##/ soundscan test");
$soundscan = new SoundScanSeed();

$soundscan
    ->addOrders()
    ->createReport()
    ->sendReport();

?>