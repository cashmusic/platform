<?php
$here = realpath('.');
$there = realpath('.') . "/../../../core/php/cashmusic.php";

define('ADMIN_BASE_PATH', $here);
define('WWW_BASE_PATH', '/admin');
define('CASH_PLATFORM_PATH', $there);
?>