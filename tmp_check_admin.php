<?php
require_once 'config/config.php';
require_once 'includes/db.php';
$stmt = $pdo->prepare("SELECT email, password_hash, role FROM users WHERE role='admin'");
$stmt->execute();
$users = $stmt->fetchAll();
if (empty($users)) {
    echo "NO ADMIN FOUND IN DATABASE.\n";
    // Check all roles to be sure
    $all = $pdo->query("SELECT email, role FROM users")->fetchAll();
    echo "Total users: " . count($all) . "\n";
    foreach($all as $u) echo $u['email'] . " (" . $u['role'] . ")\n";
} else {
    foreach($users as $u) {
        echo "Admin: " . $u['email'] . " | Hash: " . $u['password_hash'] . "\n";
    }
}
