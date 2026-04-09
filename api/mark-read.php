<?php
// api/mark-read.php
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$notif_id = (int)($_POST['notif_id'] ?? 0);

if ($user_id && $notif_id) {
    try {
        get_db()->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?")->execute([$notif_id, $user_id]);
    } catch (PDOException $e) {
        // ignore
    }
}
echo json_encode(['success' => true]);
