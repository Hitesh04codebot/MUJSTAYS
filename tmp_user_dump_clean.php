<?php
require_once 'config/config.php';
require_once 'includes/db.php';

echo "Users in database:\n";
try {
    $stmt = $pdo->query("SELECT email, role, is_active, is_verified FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        echo "Email: {$user['email']} | Role: {$user['role']} | Active: {$user['is_active']} | Verified: {$user['is_verified']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
