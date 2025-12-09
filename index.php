<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';
if (is_logged_in()) {
    header('Location: ' . APP_BASE . '/dashboard.php');
    exit;
}
header('Location: ' . APP_BASE . '/login.php');
exit;
?>