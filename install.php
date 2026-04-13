<?php
// install.php — Web-based Database Installer
require_once 'config/config.php';
require_once 'includes/db.php';

echo "<h2>MUJSTAYS Auto-Installer</h2>";

try {
    $sql = file_get_contents('install.sql');
    
    // We need to execute the SQL in chunks if exec() doesn't handle multi-query well
    // But PDO exec() on MySQL usually handles multiple statements if allowed
    $pdo->exec($sql);
    
    echo "<div style='color:green; font-weight:bold;'>✅ Database tables created and seeded successfully!</div>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
    echo "<p style='color:red;'><strong>WARNING:</strong> Please delete <code>install.php</code> from your project for security.</p>";

} catch (Exception $e) {
    echo "<div style='color:red; font-weight:bold;'>❌ Error during installation:</div>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
