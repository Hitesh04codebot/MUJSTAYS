<?php
$pdo = new PDO('mysql:host=localhost;dbname=mujstays_db', 'root', '');
$stmt = $pdo->query('SHOW COLUMNS FROM pg_listings');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['Field'] . "\n";
}
