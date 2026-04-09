<?php
require_once 'config/config.php';
require_once 'includes/db.php';
$stmt = $pdo->query('SELECT id, title, distance_from_muj, latitude, longitude FROM pg_listings');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Title: {$row['title']} | Distance: {$row['distance_from_muj']} | Lat: {$row['latitude']} | Lng: {$row['longitude']}\n";
}
