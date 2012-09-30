<?php
// include the necessary bits, define the page directory
// Define constants too
$root = dirname(__FILE__);
$cashmusic_root = realpath($root . "/../../../framework/php/cashmusic.php");

define('ADMIN_BASE_PATH', $root);
define('ADMIN_WWW_BASE_PATH', '/interfaces/php/admin');
define('CASH_PLATFORM_PATH', $cashmusic_root);
?>