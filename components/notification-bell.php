<?php
// components/notification-bell.php
// Drop-in notification dropdown (rendered inside bell-wrap in navbar)
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$notifs  = [];

if ($user_id) {
    $pdo_nb = get_db();
    $stmt = $pdo_nb->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $notifs = $stmt->fetchAll();
}

$type_icons = [
    'booking_update'   => ['icon' => '📋', 'bg' => '#EBF5FB'],
    'booking_request'  => ['icon' => '🔔', 'bg' => '#FEF9E7'],
    'payment_receipt'  => ['icon' => '💳', 'bg' => '#EAFAF1'],
    'payment_received' => ['icon' => '💰', 'bg' => '#EAFAF1'],
    'new_pg_nearby'    => ['icon' => '🏠', 'bg' => '#F5EEF8'],
    'review_posted'    => ['icon' => '⭐', 'bg' => '#FEF9E7'],
    'kyc_approved'     => ['icon' => '✅', 'bg' => '#EAFAF1'],
    'kyc_rejected'     => ['icon' => '❌', 'bg' => '#FDEDEC'],
    'listing_approved' => ['icon' => '🎉', 'bg' => '#EAFAF1'],
    'listing_rejected' => ['icon' => '⛔', 'bg' => '#FDEDEC'],
    'complaint_resolved' => ['icon' => '🔍', 'bg' => '#EBF5FB'],
    'announcement'     => ['icon' => '📢', 'bg' => '#F5EEF8'],
];
?>
<div id="notif-dropdown" class="notif-dropdown">
  <div class="notif-header">
    <span>Notifications</span>
    <?php if ($user_id): ?>
      <a href="<?= BASE_URL ?>/api/mark-all-read.php" style="font-size:12px;color:var(--accent)">Mark all read</a>
    <?php endif; ?>
  </div>

  <?php if (empty($notifs)): ?>
    <div style="padding:48px 24px;text-align:center;color:var(--text-muted)">
      <div style="width:60px;height:60px;background:var(--bg2);border-radius:50%;display:grid;place-items:center;margin:0 auto 16px;font-size:24px">🔔</div>
      <div style="font-size:14px;font-weight:600;color:var(--primary)">All caught up!</div>
      <div style="font-size:12px;margin-top:4px">No new notifications at the moment.</div>
    </div>
  <?php else: ?>
    <?php foreach ($notifs as $n):
      $meta = $type_icons[$n['type']] ?? ['icon' => '🔔', 'bg' => '#EBF5FB'];
    ?>
    <a href="<?= htmlspecialchars(format_link($n['link'])) ?>" class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>" data-notif-id="<?= $n['id'] ?>">
      <div class="notif-icon" style="background:<?= $meta['bg'] ?>"><?= $meta['icon'] ?></div>
      <div style="flex:1;min-width:0">
        <div class="notif-body-title"><?= htmlspecialchars($n['title']) ?></div>
        <div class="notif-body-text"><?= htmlspecialchars(truncate($n['body'], 60)) ?></div>
        <div class="notif-time"><?= time_ago($n['created_at']) ?></div>
      </div>
      <?php if (!$n['is_read']): ?>
        <div style="width:8px;height:8px;background:var(--accent);border-radius:50%;flex-shrink:0;margin-top:4px"></div>
      <?php endif; ?>
    </a>
    <?php endforeach; ?>
  <?php endif; ?>

  <div class="notif-footer">
    <a href="<?= BASE_URL ?>/user/notifications.php" style="font-size:13px;color:var(--accent);font-weight:600">View All Notifications →</a>
  </div>
</div>

<script>
// Mark notification as read on click
document.querySelectorAll('.notif-item[data-notif-id]').forEach(item => {
  item.addEventListener('click', function() {
    const id = this.dataset.notifId;
    fetch('<?= BASE_URL ?>/api/mark-read.php', {
      method: 'POST',
      body: new URLSearchParams({ notif_id: id }),
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    this.classList.remove('unread');
    const dot = this.querySelector('[style*="border-radius:50%"]');
    if (dot) dot.remove();
  });
});
</script>
