<?php
// owner/chat.php — Owner Chat Page
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('owner');
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Messages — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
  <?php require_once '../components/sidebar.php'; ?>
  <div class="main-content" style="padding:0;overflow:hidden">
    <?php require_once '../components/chat-widget.php'; ?>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
<meta name="csrf-token" content="<?= csrf_token() ?>">
<script src="<?= BASE_URL ?>/assets/js/chat.js"></script>
</body></html>
