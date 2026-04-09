<?php
require_once 'config/config.php';
require_once 'includes/db.php';
$p = ['Student@1234' => 'student@mujstays.com', 'Owner@1234' => 'owner@mujstays.com', 'Admin@1234' => 'admin@mujstays.com'];
foreach($p as $pass => $email) {
    $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->prepare("UPDATE users SET password_hash = ?, login_attempts = 0, locked_until = NULL WHERE email = ?")->execute([$hash, $email]);
    echo "Reset $email to $pass\n";
}
