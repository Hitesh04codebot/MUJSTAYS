<?php
// logout.php
session_start();
session_unset();
session_destroy();
$config = __DIR__ . '/config/config.php';
if (file_exists($config)) { require_once $config; header('Location: ' . BASE_URL . '/login.php'); }
else { header('Location: /login.php'); }
exit;
