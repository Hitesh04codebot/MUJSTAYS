<?php
// api/save-pg.php — Toggle save/unsave a PG listing
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

header('Content-Type: application/json');
if (!is_logged_in() || current_role()!=='student') { echo json_encode(['error'=>'Please log in as a student.']); exit; }
if (!verify_csrf()) { echo json_encode(['error'=>'Invalid request session.']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$pg_id= (int)($data['pg_id']??0);
$uid  = current_user_id();

if (!$pg_id) { echo json_encode(['error'=>'Invalid PG.']); exit; }

// Check if listing exists and is approved
$pg=$pdo->prepare("SELECT id FROM pg_listings WHERE id=? AND status='approved' AND is_deleted=0");
$pg->execute([$pg_id]);
if (!$pg->fetch()) { echo json_encode(['error'=>'PG not found.']); exit; }

// Toggle
$existing=$pdo->prepare("SELECT id FROM saved_pgs WHERE student_id=? AND pg_id=?");
$existing->execute([$uid,$pg_id]);
if ($existing->fetch()) {
    $pdo->prepare("DELETE FROM saved_pgs WHERE student_id=? AND pg_id=?")->execute([$uid,$pg_id]);
    echo json_encode(['saved'=>false,'message'=>'Removed from saved PGs']);
} else {
    $pdo->prepare("INSERT INTO saved_pgs (student_id,pg_id) VALUES (?,?)")->execute([$uid,$pg_id]);
    echo json_encode(['saved'=>true,'message'=>'Saved to your favourites! ♥']);
}
