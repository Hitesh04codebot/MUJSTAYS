<?php
require_once 'config/config.php';
require_once 'includes/db.php';
$stmt = $pdo->prepare("SELECT email, password_hash, role, login_attempts, locked_until FROM users WHERE email='owner@mujstays.com'");
$stmt->execute();
$u = $stmt->fetch();
if ($u) {
    echo "Email: " . $u['email'] . "\n";
    echo "Hash: " . $u['password_hash'] . "\n";
    echo "Attempts: " . $u['login_attempts'] . "\n";
    echo "Locked Until: " . $u['locked_until'] . "\n";
    
    // Test verify
    $pass = 'Owner@1234';
    echo "Verifying 'Owner@1234': " . (password_verify($pass, $u['password_hash']) ? "PASS" : "FAIL") . "\n";
} else {
    echo "Owner not found.\n";
}
