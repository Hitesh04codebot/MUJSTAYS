<?php
require_once 'config/config.php';
require_once 'includes/db.php';
$stmt=$pdo->query('DESCRIBE bookings');
foreach($stmt->fetchAll() as $r) echo $r['Field']."\n";
