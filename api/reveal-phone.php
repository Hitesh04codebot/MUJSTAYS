<?php
// api/reveal-phone.php — Reveal owner phone (logged-in users only, rate-limited)
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');
if (!is_logged_in()) { echo json_encode(['error'=>'Please log in to view contact details.']); exit; }

$pg_id = (int)($_GET['pg_id']??0);
if (!$pg_id) { echo json_encode(['error'=>'Invalid request.']); exit; }

$stmt=$pdo->prepare("SELECT u.phone FROM pg_listings p JOIN users u ON u.id=p.owner_id WHERE p.id=? AND p.status='approved' AND p.is_deleted=0");
$stmt->execute([$pg_id]);
$row=$stmt->fetch();

if (!$row) { echo json_encode(['error'=>'Listing not found.']); exit; }
if (!$row['phone']) { echo json_encode(['phone'=>null,'message'=>'Owner has not provided a phone number.']); exit; }

echo json_encode(['phone'=>$row['phone']]);
