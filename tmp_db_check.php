<?php
require_once 'config/config.php';
require_once 'includes/db.php';

echo "Database: " . DB_NAME . "\n";
echo "Tables:\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo $row[0] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
