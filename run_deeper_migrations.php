<?php
require_once 'config/config.php';
require_once 'includes/db.php';

try {
    echo "Running deeper migrations...\n";

    // 1. Ensure areas table has initial data
    $pdo->exec("INSERT IGNORE INTO areas (name, distance_from_muj) VALUES 
        ('Jagatpura', 0.5), ('Govindpura', 2.1), ('Sitapura', 5.2), ('Tonk Road', 3.5), ('Agra Road', 4.8)");

    // 2. Fix pg_listings table
    $stmt = $pdo->query("DESCRIBE pg_listings");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('area_id', $columns)) {
        echo "Adding 'area_id' column to 'pg_listings'...\n";
        $pdo->exec("ALTER TABLE pg_listings ADD COLUMN area_id INT UNSIGNED AFTER owner_id");
    }

    // 3. Migrate area_name to area_id
    if (in_array('area_name', $columns)) {
        echo "Migrating 'area_name' values to 'area_id'...\n";
        $areas = $pdo->query("SELECT id, name FROM areas")->fetchAll(PDO::FETCH_KEY_PAIR);
        $stmt = $pdo->prepare("UPDATE pg_listings SET area_id = ? WHERE area_name = ?");
        foreach ($areas as $id => $name) {
            $stmt->execute([$id, $name]);
        }
        echo "Updating rows with no match to default (1)...\n";
        $pdo->exec("UPDATE pg_listings SET area_id = 1 WHERE area_id IS NULL");
    }

    // 4. Ensure demo users exist
    echo "Creating demo users...\n";
    $users = [
        ['admin@mujstays.com', 'admin', 'Admin@1234'],
        ['student@mujstays.com', 'student', 'Student@1234'],
        ['owner@mujstays.com', 'owner', 'Owner@1234']
    ];
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $insert = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, is_active, is_verified) VALUES (?, ?, ?, ?, 1, 1)");
    
    foreach ($users as $u) {
        $stmt->execute([$u[0]]);
        if (!$stmt->fetch()) {
            echo "Inserting user {$u[0]}...\n";
            $name = ucfirst(explode('@', $u[0])[0]);
            $hash = password_hash($u[2], PASSWORD_DEFAULT);
            $insert->execute([$name, $u[0], $hash, $u[1]]);
        }
    }

    echo "Migrations completed successfully!\n";

} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
