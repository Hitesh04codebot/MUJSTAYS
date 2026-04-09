<?php
require 'config/config.php';
require 'includes/db.php';
$pdo = get_db();
$pdo->exec('UPDATE users SET login_attempts = 0, last_attempt = NULL');
echo 'Lockouts cleared';
unlink(__FILE__); // Self-destruct
