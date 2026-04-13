<?php
// user/notifications.php — Student Notifications
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('student');
$uid = current_user_id();

// Handle "Mark all as read"
if (isset($_GET['mark_all_read']) && verify_csrf()) {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")->execute([$uid]);
    flash_set('success', 'All notifications marked as read.');
    redirect('notifications.php');
}

// Handle single notification deletion or mark as read (optional, let's keep it simple)
if (isset($_GET['read']) && verify_csrf()) {
    $nid = (int)$_GET['read'];
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?")->execute([$nid, $uid]);
}

// Fetch notifications
$total = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
$total->execute([$uid]);
$total = (int)$total->fetchColumn();

$pag = paginate($total, 15);
$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT {$pag['per_page']} OFFSET {$pag['offset']}");
$notifs->execute([$uid]);
$notifs = $notifs->fetchAll();

// Get unread count for sidebar
$notif_count = unread_notification_count($pdo, $uid);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Notifications — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  .notification-item { padding: 16px 20px; border-bottom: 1px solid var(--border); transition: background .2s; border-left: 4px solid transparent; }
  .notification-item.unread { background: #f0f7ff; border-left-color: var(--accent); }
  .notification-item:hover { background: var(--bg2); }
  .notif-icon { width: 40px; height: 40px; border-radius: 50%; display: grid; place-items: center; font-size: 18px; flex-shrink: 0; }
  .notif-icon.booking { background: #e6fffa; color: #319795; }
  .notif-icon.payment { background: #fffaf0; color: #dd6b20; }
  .notif-icon.info { background: #ebf8ff; color: #3182ce; }
  .notif-time { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
</style>
</head><body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-logo"><h3>🎓 Student</h3><p><?= htmlspecialchars($_SESSION['name']) ?></p></div>
    <nav class="sidebar-menu">
      <div class="sidebar-section">Main</div>
      <a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="bookings.php"  class="sidebar-link"><i class="fas fa-calendar-check"></i> My Bookings</a>
      <a href="saved.php"     class="sidebar-link"><i class="fas fa-heart"></i> Saved PGs</a>
      <div class="sidebar-section">Activity</div>
      <a href="chat.php"          class="sidebar-link"><i class="fas fa-comments"></i> Messages</a>
      <a href="notifications.php" class="sidebar-link active"><i class="fas fa-bell"></i> Notifications <?php if ($notif_count): ?><span class="badge badge-danger" style="font-size:10px;margin-left:auto"><?= $notif_count ?></span><?php endif; ?></a>
      <a href="reviews.php"   class="sidebar-link"><i class="fas fa-star"></i> My Reviews</a>
    </nav>
  </div>

  <div class="main-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
      <h2 style="margin:0">Notifications</h2>
      <?php if ($notif_count): ?>
        <a href="?mark_all_read=1&csrf_token=<?= csrf_token() ?>" class="btn btn-outline btn-sm"><i class="fas fa-check-double"></i> Mark All as Read</a>
      <?php endif; ?>
    </div>

    <?php if ($msg = flash_get('success')): ?><div class="alert alert-success mt-16 mb-16"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <div class="card overflow-hidden">
      <?php if (empty($notifs)): ?>
        <div class="empty-state" style="padding:60px">
          <i class="fas fa-bell-slash" style="font-size:48px;color:var(--border);margin-bottom:16px"></i>
          <h3>No notifications yet</h3>
          <p>We'll notify you here about your bookings, payments, and messages.</p>
        </div>
      <?php else: ?>
        <div style="display:flex;flex-direction:column">
          <?php foreach ($notifs as $n): 
            $icon_class = 'info'; $icon = 'info-circle';
            if (strpos($n['type'], 'booking') !== false) { $icon_class = 'booking'; $icon = 'calendar-check'; }
            if (strpos($n['type'], 'payment') !== false) { $icon_class = 'payment'; $icon = 'money-bill-wave'; }
            
            $final_link = $n['link'] ? format_link($n['link']) : '#';
            if ($n['link'] && !preg_match('/^(https?:\/\/|ftp:\/\/|\/\/)/i', $n['link'])) {
                $final_link .= (str_contains($final_link, '?') ? '&' : '?') . "read=" . $n['id'] . "&csrf_token=" . csrf_token();
            }
          ?>
          <a href="<?= $n['link'] ? htmlspecialchars($final_link) : 'javascript:void(0)' ?>" class="notification-item <?= $n['is_read'] ? '' : 'unread' ?>" style="text-decoration:none; display:block; padding:20px; border-bottom:1px solid var(--border); transition: var(--transition); cursor: <?= $n['link'] ? 'pointer' : 'default' ?>">
            <div style="display:flex;gap:16px;align-items:flex-start">
              <div class="notif-icon <?= $icon_class ?>" style="flex-shrink:0"><i class="fas fa-<?= $icon ?>"></i></div>
              <div style="flex:1; min-width:0">
                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                  <h4 style="margin:0;font-size:15px;font-weight:700;color:var(--primary)"><?= htmlspecialchars($n['title']) ?></h4>
                  <?php if (!$n['is_read']): ?>
                    <span class="badge badge-accent" style="font-size:10px">NEW</span>
                  <?php endif; ?>
                </div>
                <p style="margin:6px 0 0 0;font-size:14px;color:var(--text);line-height:1.5"><?= htmlspecialchars($n['body']) ?></p>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px">
                    <div class="notif-time" style="font-size:12px; color:var(--text-light)" title="<?= $n['created_at'] ?>"><i class="far fa-clock"></i> <?= time_ago($n['created_at']) ?></div>
                    <?php if ($n['link']): ?>
                        <span style="font-size:12px; color:var(--accent); font-weight:600">View Details <i class="fas fa-arrow-right"></i></span>
                    <?php endif; ?>
                </div>
              </div>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
        <div style="padding:16px"><?= pagination_html($pag, 'notifications.php') ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
</body></html>
