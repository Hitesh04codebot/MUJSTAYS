<?php
require_once 'config/config.php';
require_once 'includes/db.php';
$stmt=$pdo->query('DESCRIBE users');
foreach($stmt->fetchAll() as $r) echo $r['Field']."\n";
