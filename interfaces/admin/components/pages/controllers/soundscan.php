<?php

error_log("##/ soundscan test");
$soundscan = new SoundScanSeed();

$soundscan->addOrders();

error_log(
    print_r(
        $soundscan->orders, true
    )
)

?>