<?php
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

$stmt = $pdo->query('SELECT id, latitude, longitude, distance_from_muj FROM pg_listings');
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $new_dist = distance_from_muj($row['latitude'], $row['longitude']);
    echo "ID: {$row['id']} | Old: {$row['distance_from_muj']} | New: {$new_dist}\n";
    if ($new_dist != $row['distance_from_muj']) {
        $pdo->prepare('UPDATE pg_listings SET distance_from_muj = ? WHERE id = ?')->execute([$new_dist, $row['id']]);
    }
}
