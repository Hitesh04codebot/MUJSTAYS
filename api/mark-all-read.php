<?php
// api/mark-all-read.php
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id) {
    try {
        get_db()->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
    } catch (PDOException $e) {
        // ignore
    }
}

// Redirect back to the page the user was on
$ref = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/index.php');
redirect($ref);
