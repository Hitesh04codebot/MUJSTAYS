<?php
require_once 'config/config.php';
require_once 'includes/db.php';
try {
    $pdo->exec("ALTER TABLE kyc_documents ADD COLUMN IF NOT EXISTS rejection_reason TEXT AFTER status");
    // Also rename created_at to submitted_at to match the code's expectation if possible, 
    // or just add submitted_at as an alias (via a new column).
    // Actually, it's safer to just fix the code in profile.php to use created_at.
    echo "Success";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
