<?php
$here = realpath('.');
$there = realpath('.') . "/../../Seed.php";

define('ADMIN_BASE_PATH', $here);
define('WWW_BASE_PATH', '/admin');
define('SEED_PATH', $there);
?>