<?php
require_once 'config/config.php';
require_once 'includes/db.php';

try {
    echo "Testing query in index.php...\n";
    $stmt = $pdo->query("
        SELECT p.*, a.name AS area_name
        FROM pg_listings p
        JOIN areas a ON p.area_id = a.id
        LIMIT 1
    ");
    $res = $stmt->fetch();
    print_r($res);
    echo "Test passed!\n";
} catch (Exception $e) {
    echo "Query failed: " . $e->getMessage() . "\n";
}
