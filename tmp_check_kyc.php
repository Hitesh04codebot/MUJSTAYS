<?php
require_once 'config/config.php';
require_once 'includes/db.php';
$stmt = $pdo->query('SHOW COLUMNS FROM kyc_documents');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . "\n";
}
