<?php

$logger = new class
{
    public function log(string $log)
    {
        echo $log;
    }
}
