<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=mujstays_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE pg_listings ADD COLUMN has_transport TINYINT(1) DEFAULT 0 AFTER has_warden");
    echo "Column added successfully.";
} catch (PDOException $e) {
    echo "Error adding column: " . $e->getMessage();
}
