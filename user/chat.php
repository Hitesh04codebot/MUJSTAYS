<?php
// user/chat.php — Student Chat Page
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('student');
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Messages — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
  <div class="sidebar">
    <div class="sidebar-logo"><h3>🎓 Student</h3><p><?= htmlspecialchars($_SESSION['name']) ?></p></div>
    <nav class="sidebar-menu">
      <a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="bookings.php"  class="sidebar-link"><i class="fas fa-calendar-check"></i> My Bookings</a>
      <a href="saved.php"     class="sidebar-link"><i class="fas fa-heart"></i> Saved PGs</a>
      <a href="chat.php"      class="sidebar-link active"><i class="fas fa-comments"></i> Messages</a>
      <a href="notifications.php" class="sidebar-link"><i class="fas fa-bell"></i> Notifications</a>
      <a href="reviews.php"   class="sidebar-link"><i class="fas fa-star"></i> Reviews</a>
      <a href="payments.php"  class="sidebar-link"><i class="fas fa-receipt"></i> Payments</a>
      <a href="profile.php"   class="sidebar-link"><i class="fas fa-user"></i> Profile</a>
      <a href="<?= BASE_URL ?>/logout.php" class="sidebar-link" style="color:rgba(255,100,100,.8)"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </div>
  <div class="main-content" style="padding:0;overflow:hidden">
    <?php require_once '../components/chat-widget.php'; ?>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
<meta name="csrf-token" content="<?= csrf_token() ?>">
<script>var BASE_URL='<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<script src="<?= BASE_URL ?>/assets/js/chat.js"></script>
</body></html>
