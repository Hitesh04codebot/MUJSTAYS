<?php
// api/send-message.php — Send a chat message
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

header('Content-Type: application/json');
if (!is_logged_in()) { echo json_encode(['error'=>'Unauthorized']); exit; }

$receiver_id  = (int)($_POST['receiver_id']??0);
$message_text = trim($_POST['message_text']??'');
$pg_id        = (int)($_POST['pg_id']??0) ?: null;
$sender_id    = current_user_id();

if (!$receiver_id||!$message_text) { echo json_encode(['error'=>'Missing fields']); exit; }
if (strlen($message_text)>2000) { echo json_encode(['error'=>'Message too long']); exit; }
if ($receiver_id===$sender_id) { echo json_encode(['error'=>'Cannot message yourself']); exit; }

// Verify receiver exists
$r=$pdo->prepare("SELECT id FROM users WHERE id=? AND is_deleted=0"); $r->execute([$receiver_id]);
if (!$r->fetch()) { echo json_encode(['error'=>'Recipient not found']); exit; }

$stmt=$pdo->prepare("INSERT INTO messages (sender_id,receiver_id,pg_id,message_text) VALUES (?,?,?,?)");
$stmt->execute([$sender_id,$receiver_id,$pg_id,$message_text]);
$msg_id=$pdo->lastInsertId();

echo json_encode([
    'success'=>true,
    'message'=>[
        'id'           => $msg_id,
        'sender_id'    => $sender_id,
        'receiver_id'  => $receiver_id,
        'message_text' => $message_text,
        'sent_at'      => date('Y-m-d H:i:s'),
        'is_sent'      => true,
    ]
]);
