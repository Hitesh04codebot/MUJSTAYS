<?php
require_once 'config/config.php';
require_once 'includes/db.php';

echo "Users in database:\n";
try {
    $stmt = $pdo->query("SELECT email, role, is_active, is_verified FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
