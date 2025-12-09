<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'checkinventorydb');
define('DB_USER', 'root');
define('DB_PASS', 'asdfQWER789');
define('APP_NAME', 'Packing Quantity Checker');
$__script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\','/', $_SERVER['SCRIPT_NAME']) : '';
$__dir = rtrim(dirname($__script), '/');
$__base = preg_replace('#/pages$#', '', $__dir);
define('APP_BASE', $__base);
$__scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$__host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('APP_URL', $__scheme . '://' . $__host . $__base);
?>