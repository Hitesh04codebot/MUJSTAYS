<?php
require_once 'config/config.php';
require_once 'includes/db.php';

try {
    $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL");
    $stmt->execute();
    echo "Successfully cleared all login lockouts.\n";
} catch (PDOException $e) {
    echo "Error clearing lockouts: " . $e->getMessage() . "\n";
}
