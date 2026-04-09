<?php
require_once 'config/config.php';
require_once 'includes/db.php';
$u = $pdo->query("SELECT email, role FROM users ORDER BY id DESC LIMIT 10")->fetchAll();
echo "Last 10 users:\n";
foreach($u as $row) echo $row['email'] . " (" . $row['role'] . ")\n";
