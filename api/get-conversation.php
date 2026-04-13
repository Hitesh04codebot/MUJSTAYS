<?php
// api/get-conversation.php — Fetch full message history between two users
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$current_uid = current_user_id();
$partner_id = (int)($_GET['with'] ?? 0);
$pg_id = (int)($_GET['pg_id'] ?? 0);

if (!$partner_id) {
    echo json_encode(['error' => 'Partner ID required']);
    exit;
}

try {
    // Fetch messages
    $query = "SELECT m.*, u.name AS sender_name 
              FROM messages m 
              JOIN users u ON u.id = m.sender_id 
              WHERE ((m.sender_id = ? AND m.receiver_id = ?) 
                 OR (m.sender_id = ? AND m.receiver_id = ?))";
    
    $params = [$current_uid, $partner_id, $partner_id, $current_uid];
    
    if ($pg_id) {
        $query .= " AND m.pg_id = ?";
        $params[] = $pg_id;
    }
    
    $query .= " ORDER BY m.sent_at ASC LIMIT 150";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $messages = $stmt->fetchAll();

    // Map messages for frontend
    $result_messages = [];
    $last_id = 0;
    foreach ($messages as $msg) {
        $result_messages[] = [
            'id' => $msg['id'],
            'sender_id' => $msg['sender_id'],
            'message_text' => $msg['message_text'],
            'sent_at' => $msg['sent_at'],
            'is_sent' => ($msg['sender_id'] == $current_uid)
        ];
        $last_id = max($last_id, (int)$msg['id']);
    }

    // Mark as read
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0")
        ->execute([$partner_id, $current_uid]);

    echo json_encode([
        'success' => true,
        'messages' => $result_messages,
        'last_id' => $last_id
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
}
