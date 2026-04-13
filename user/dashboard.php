<?php
// user/dashboard.php — Student Dashboard
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('student');
$uid = current_user_id();

// Saved PGs (last 4)
$saved = $pdo->prepare("SELECT p.*, (SELECT file_path FROM pg_images WHERE pg_id=p.id AND is_cover=1 LIMIT 1) AS cover_image FROM saved_pgs s JOIN pg_listings p ON p.id=s.pg_id WHERE s.student_id=? AND p.is_deleted=0 ORDER BY s.saved_at DESC LIMIT 4");
$saved->execute([$uid]); $saved_pgs = $saved->fetchAll();

// Recent bookings (last 5)
$bk = $pdo->prepare("SELECT b.*, p.title AS pg_title, p.area_name, rt.type AS room_type FROM bookings b JOIN pg_listings p ON p.id=b.pg_id JOIN room_types rt ON rt.id=b.room_type_id WHERE b.student_id=? ORDER BY b.created_at DESC LIMIT 5");
$bk->execute([$uid]); $bookings = $bk->fetchAll();

// Notifications count
$notif_count = unread_notification_count($pdo, $uid);

// Stats
$stats_q = $pdo->prepare("SELECT (SELECT COUNT(*) FROM saved_pgs WHERE student_id=?) AS saved_count, (SELECT COUNT(*) FROM bookings WHERE student_id=?) AS total_bookings, (SELECT COUNT(*) FROM bookings WHERE student_id=? AND status='confirmed') AS active_bookings, (SELECT COUNT(*) FROM reviews WHERE student_id=?) AS reviews_given");
$stats_q->execute([$uid,$uid,$uid,$uid]); $stats = $stats_q->fetch();

$page_title = 'My Dashboard';
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Dashboard — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
  <!-- Sidebar -->
  <?php require_once '../components/sidebar.php'; ?>

  <!-- Main -->
  <div class="main-content">
    <?php if ($msg = flash_get('success')): ?><div class="alert alert-success" data-dismiss="4000"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <div style="margin-bottom:28px">
      <h2 style="margin-bottom:4px">Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['name'])[0]) ?>! 👋</h2>
      <p style="color:var(--text-muted)">Here's what's happening with your PG search.</p>
    </div>

    <!-- Stats -->
    <div class="admin-grid" style="margin-bottom:32px">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-heart"></i></div>
        <div><div class="stat-num"><?= $stats['saved_count'] ?></div><div class="stat-label">Saved PGs</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-calendar-check"></i></div>
        <div><div class="stat-num"><?= $stats['total_bookings'] ?></div><div class="stat-label">Total Bookings</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-home"></i></div>
        <div><div class="stat-num"><?= $stats['active_bookings'] ?></div><div class="stat-label">Active Stays</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-star"></i></div>
        <div><div class="stat-num"><?= $stats['reviews_given'] ?></div><div class="stat-label">Reviews Given</div></div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
      <!-- Recent Bookings -->
      <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
          <h3 style="margin:0;font-size:16px"><i class="fas fa-calendar" style="color:var(--accent)"></i> Recent Bookings</h3>
          <a href="bookings.php" class="btn btn-ghost btn-sm">View All →</a>
        </div>
        <?php if (empty($bookings)): ?>
          <div class="empty-state" style="padding:40px"><i class="fas fa-calendar-times"></i><p>No bookings yet. <a href="<?= BASE_URL ?>/explore.php">Find a PG →</a></p></div>
        <?php else: ?>
          <div style="padding:0">
            <?php foreach ($bookings as $b): ?>
            <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
              <div>
                <div style="font-weight:600;font-size:14px;color:var(--primary)"><?= htmlspecialchars(truncate($b['pg_title'],30)) ?></div>
                <div style="font-size:12px;color:var(--text-muted)"><?= ucfirst($b['room_type']) ?> · Move-in: <?= date('d M Y',strtotime($b['move_in_date'])) ?></div>
              </div>
              <?= status_badge($b['status']) ?>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Saved PGs -->
      <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
          <h3 style="margin:0;font-size:16px"><i class="fas fa-heart" style="color:var(--danger)"></i> Saved PGs</h3>
          <a href="saved.php" class="btn btn-ghost btn-sm">View All →</a>
        </div>
        <?php if (empty($saved_pgs)): ?>
          <div class="empty-state" style="padding:40px"><i class="fas fa-heart-broken"></i><p>No saved PGs yet. <a href="<?= BASE_URL ?>/explore.php">Browse PGs →</a></p></div>
        <?php else: ?>
          <div style="padding:8px">
            <?php foreach ($saved_pgs as $pg): ?>
            <a href="<?= BASE_URL ?>/pg-detail.php?id=<?= $pg['id'] ?>" style="display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;margin-bottom:4px;text-decoration:none;transition:background .2s" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''">
              <img src="<?= $pg['cover_image'] ? BASE_URL.'/'.$pg['cover_image'] : BASE_URL.'/assets/images/pg-placeholder.jpg' ?>" alt="" style="width:50px;height:40px;border-radius:6px;object-fit:cover;flex-shrink:0">
              <div style="flex:1;min-width:0">
                <div style="font-weight:600;font-size:13px;color:var(--primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($pg['title']) ?></div>
                <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($pg['area_name']) ?> · <?= format_currency($pg['price_min']) ?>/mo</div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card" style="margin-top:24px">
      <div class="card-body">
        <h3 style="margin-bottom:16px">Quick Actions</h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <a href="<?= BASE_URL ?>/explore.php" class="btn btn-primary"><i class="fas fa-search"></i> Find PGs</a>
          <a href="saved.php" class="btn btn-outline"><i class="fas fa-heart"></i> Saved PGs</a>
          <a href="compare.php" class="btn btn-outline"><i class="fas fa-balance-scale"></i> Compare</a>
          <a href="chat.php" class="btn btn-outline"><i class="fas fa-comments"></i> Messages</a>
          <a href="profile.php" class="btn btn-ghost"><i class="fas fa-user-edit"></i> Edit Profile</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
<script>var BASE_URL='<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
