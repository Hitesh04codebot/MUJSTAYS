<?php
// api/messages-poll.php — Long-poll for new messages
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');
if (!is_logged_in()) { echo json_encode(['messages'=>[]]); exit; }

$uid         = current_user_id();
$receiver_id = (int)($_GET['receiver_id']??0);
$after       = (int)($_GET['after']??0);
$pg_id       = (int)($_GET['pg_id']??0) ?: null;

if (!$receiver_id) { echo json_encode(['messages'=>[]]); exit; }

$max_wait = 15; // Wait up to 15 seconds
$start = time();
$messages = [];

while (time() - $start < $max_wait) {
    $stmt = $pdo->prepare("SELECT m.*, u.name AS sender_name FROM messages m JOIN users u ON u.id=m.sender_id WHERE m.sender_id=? AND m.receiver_id=? AND m.id>? ORDER BY m.sent_at ASC LIMIT 50");
    $stmt->execute([$receiver_id, $uid, $after]);
    $messages = $stmt->fetchAll();
    
    if (!empty($messages)) break;
    
    usleep(500000); // Wait 0.5s before retry
}

// Mark as read
$pdo->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0")->execute([$receiver_id,$uid]);

// Unread notification count
$nc=$pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$nc->execute([$uid]); $unread_count=(int)$nc->fetchColumn();

echo json_encode(['messages'=>$messages,'unread_count'=>$unread_count]);
