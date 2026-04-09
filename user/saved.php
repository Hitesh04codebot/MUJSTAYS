<?php
// user/saved.php — Student Saved / Wishlist PGs
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('student');
$uid = current_user_id();

// Handle unsave
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $pid = (int)($_POST['pg_id']??0);
    $pdo->prepare("DELETE FROM saved_pgs WHERE student_id=? AND pg_id=?")->execute([$uid,$pid]);
    header('Location: saved.php'); exit;
}

$total=$pdo->prepare("SELECT COUNT(*) FROM saved_pgs s JOIN pg_listings p ON p.id=s.pg_id WHERE s.student_id=? AND p.is_deleted=0");
$total->execute([$uid]); $total=(int)$total->fetchColumn();
$pag=paginate($total,12);

$stmt=$pdo->prepare("SELECT p.*, s.saved_at, (SELECT file_path FROM pg_images WHERE pg_id=p.id AND is_cover=1 LIMIT 1) AS cover_image FROM saved_pgs s JOIN pg_listings p ON p.id=s.pg_id WHERE s.student_id=? AND p.is_deleted=0 ORDER BY s.saved_at DESC LIMIT {$pag['per_page']} OFFSET {$pag['offset']}");
$stmt->execute([$uid]); $saved=$stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Saved PGs — MUJSTAYS</title>
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
      <a href="saved.php"     class="sidebar-link active"><i class="fas fa-heart"></i> Saved PGs</a>
      <a href="compare.php"   class="sidebar-link"><i class="fas fa-balance-scale"></i> Compare</a>
      <a href="chat.php"      class="sidebar-link"><i class="fas fa-comments"></i> Messages</a>
      <a href="notifications.php" class="sidebar-link"><i class="fas fa-bell"></i> Notifications</a>
      <a href="reviews.php"   class="sidebar-link"><i class="fas fa-star"></i> Reviews</a>
      <a href="payments.php"  class="sidebar-link"><i class="fas fa-receipt"></i> Payments</a>
      <a href="profile.php"   class="sidebar-link"><i class="fas fa-user"></i> Profile</a>
      <a href="<?= BASE_URL ?>/logout.php" class="sidebar-link" style="color:rgba(255,100,100,.8)"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </div>
  <div class="main-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
      <h2 style="margin:0">Saved PGs <span style="font-size:16px;color:var(--text-muted);font-weight:400">(<?= $total ?>)</span></h2>
      <a href="<?= BASE_URL ?>/explore.php" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Browse More PGs</a>
    </div>
    <?php if (empty($saved)): ?>
      <div class="empty-state">
        <i class="fas fa-heart-broken"></i>
        <h3>No Saved PGs Yet</h3>
        <p>Tap the ♡ icon on any PG listing to save it for later.</p>
        <a href="<?= BASE_URL ?>/explore.php" class="btn btn-primary">Explore PGs</a>
      </div>
    <?php else: ?>
      <div class="pg-grid">
        <?php foreach($saved as $pg): ?>
          <?php require '../components/pg-card.php'; ?>
        <?php endforeach; ?>
      </div>
      <?= pagination_html($pag,'?') ?>
    <?php endif; ?>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
<script>var BASE_URL='<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
