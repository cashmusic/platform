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

$client = new Raven_Client('https://319ebcf106aa451faf4e1d3d7605b3de@sentry.io/252348');

$error_handler = new Raven_ErrorHandler($client);
$error_handler->registerExceptionHandler();
$error_handler->registerErrorHandler();
$error_handler->registerShutdownFunction();

$controller = new \CASHMusic\Admin\AdminController();