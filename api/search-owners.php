<?php
// api/search-owners.php — Search for PG owners to start a new chat
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

header('Content-Type: application/json');

if (!is_logged_in() || current_role() !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$query = $_GET['q'] ?? '';
if (strlen($query) < 2) {
    echo json_encode(['success' => true, 'owners' => []]);
    exit;
}

$pdo = get_db();
// Only search for users with the 'owner' role and who are active
$stmt = $pdo->prepare("
    SELECT id, name, profile_photo 
    FROM users 
    WHERE role = 'owner' AND is_active = 1 AND name LIKE ? 
    LIMIT 10
");
$stmt->execute(['%' . $query . '%']);
$owners = $stmt->fetchAll();

echo json_encode(['success' => true, 'owners' => $owners]);
