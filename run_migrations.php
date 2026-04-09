<?php
require_once 'config/config.php';
require_once 'includes/db.php';

try {
    echo "Processing migrations...\n";

    // 1. Add is_deleted to users
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('is_deleted', $columns)) {
        echo "Adding 'is_deleted' column to 'users' table...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN is_deleted TINYINT(1) DEFAULT 0 AFTER updated_at");
    } else {
        echo "'is_deleted' column already exists in 'users' table.\n";
    }

    // 2. Create areas table if missing
    echo "Checking 'areas' table...\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'areas'");
    if ($stmt->rowCount() == 0) {
        echo "Creating 'areas' table...\n";
        $pdo->exec("CREATE TABLE `areas` (
          `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `name` VARCHAR(100) NOT NULL,
          `distance_from_muj` DECIMAL(5,2) DEFAULT NULL,
          PRIMARY KEY (`id`),
          UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Seed default areas
        echo "Seeding default areas...\n";
        $areas = [
            ['Jagatpura', 0.50],
            ['Govindpura', 2.10],
            ['Sitapura', 5.20],
            ['Tonk Road', 3.50],
            ['Agra Road', 4.80]
        ];
        $stmt = $pdo->prepare("INSERT IGNORE INTO areas (name, distance_from_muj) VALUES (?, ?)");
        foreach ($areas as $area) {
            $stmt->execute($area);
        }
    } else {
        echo "'areas' table already exists.\n";
    }

    echo "Migrations completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
