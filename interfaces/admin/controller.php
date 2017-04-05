<?php

/*register_shutdown_function( "fatal_handler" );

function fatal_handler() {
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;

    $error = error_get_last();

    if( $error !== NULL) {

        error_log(
            "\ntype: ". $error['type'] .
                "\nfile: " . $error['file'].
                "\nline: " . $error['line'].
                "\nmessage: " . $error['message'].
                "\nstacktrace: " . json_encode(debug_backtrace())
        );
    }
}*/

require_once(__DIR__ . '/constants.php');
require_once($root.'/../../vendor/autoload.php');

$controller = new \CASHMusic\Admin\AdminController();