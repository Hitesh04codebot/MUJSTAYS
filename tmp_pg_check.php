<?php
require_once 'config/config.php';
require_once 'includes/db.php';

echo "Columns in pg_listings table:\n";
try {
    $stmt = $pdo->query("DESCRIBE pg_listings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
