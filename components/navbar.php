<?php
// components/navbar.php — Global navigation bar
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

$is_logged = !empty($_SESSION['user_id']);
$role      = $_SESSION['role'] ?? '';
$user_name = $_SESSION['name'] ?? '';
$user_id   = (int)($_SESSION['user_id'] ?? 0);

$unread_count = 0;
if ($is_logged && $user_id) {
    $pdo_nav = get_db();
    $stmt = $pdo_nav->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unread_count = (int)$stmt->fetchColumn();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
  <div class="container">
    <div class="navbar-inner">
      <a href="<?= BASE_URL ?>" class="navbar-brand">
        <div class="brand-icon">🏠</div>
        MUJ<span>STAYS</span>
      </a>

      <ul class="navbar-nav">
        <li><a href="<?= BASE_URL ?>/index.php" class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>">Home</a></li>
        <li><a href="<?= BASE_URL ?>/explore.php" class="nav-link <?= $current_page === 'explore.php' ? 'active' : '' ?>">Explore PGs</a></li>
        <li><a href="<?= BASE_URL ?>/about.php" class="nav-link <?= $current_page === 'about.php' ? 'active' : '' ?>">About</a></li>
        <li><a href="<?= BASE_URL ?>/contact.php" class="nav-link <?= $current_page === 'contact.php' ? 'active' : '' ?>">Contact</a></li>

        <?php if ($is_logged): ?>
          <div class="nav-divider"></div>

          <?php if ($role === 'student'): ?>
            <li><a href="<?= BASE_URL ?>/user/bookings.php" class="nav-link <?= $current_page === 'bookings.php' ? 'active' : '' ?>">My Bookings</a></li>
            <li><a href="<?= BASE_URL ?>/user/saved.php"   class="nav-link <?= $current_page === 'saved.php'    ? 'active' : '' ?>">Saved</a></li>
          <?php elseif ($role === 'owner'): ?>
            <li><a href="<?= BASE_URL ?>/owner/listings.php" class="nav-link <?= $current_page === 'listings.php' ? 'active' : '' ?>">My Listings</a></li>
            <li><a href="<?= BASE_URL ?>/owner/bookings.php" class="nav-link <?= $current_page === 'bookings.php' ? 'active' : '' ?>">Bookings</a></li>
          <?php elseif ($role === 'admin'): ?>
            <li><a href="<?= BASE_URL ?>/admin/dashboard.php" class="nav-link">Admin Panel</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>

      <div class="navbar-actions">
        <?php if ($is_logged): ?>
          <!-- Notification Bell -->
          <div class="bell-wrap" style="position:relative">
            <button id="bell-btn" class="btn btn-ghost btn-icon" title="Notifications" aria-label="Notifications">
              <i class="fas fa-bell" style="font-size:18px;color:var(--primary)"></i>
              <?php if ($unread_count > 0): ?>
                <span class="bell-badge"><?= $unread_count > 9 ? '9+' : $unread_count ?></span>
              <?php endif; ?>
            </button>
            <?php require_once __DIR__ . '/notification-bell.php'; ?>
          </div>

          <!-- Profile Dropdown -->
          <div style="position:relative">
            <button id="profile-btn" class="btn btn-ghost" style="display:flex;align-items:center;gap:8px;padding:8px 12px">
              <div style="width:32px;height:32px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0">
                <?= strtoupper(mb_substr($user_name, 0, 1)) ?>
              </div>
              <span style="font-size:14px;font-weight:600;color:var(--primary)"><?= htmlspecialchars(explode(' ', $user_name)[0]) ?></span>
              <i class="fas fa-chevron-down" style="font-size:11px;color:var(--text-muted)"></i>
            </button>
            <div id="profile-menu" style="display:none;position:absolute;right:0;top:calc(100% + 8px);background:#fff;border-radius:var(--radius-lg);border:1px solid var(--border);box-shadow:var(--shadow-lg);min-width:200px;z-index:500;overflow:hidden">
              <div style="padding:14px 16px;border-bottom:1px solid var(--border)">
                <div style="font-weight:700;font-size:14px;color:var(--primary)"><?= htmlspecialchars($user_name) ?></div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px"><?= ucfirst($role) ?> Account</div>
              </div>
              <?php
              $dash = match($role) {
                'student' => BASE_URL . '/user/dashboard.php',
                'owner'   => BASE_URL . '/owner/dashboard.php',
                'admin'   => BASE_URL . '/admin/dashboard.php',
                default   => BASE_URL . '/',
              };
              $profile = match($role) {
                'student' => BASE_URL . '/user/profile.php',
                'owner'   => BASE_URL . '/owner/profile.php',
                default   => '',
              };
              ?>
              <a href="<?= $dash ?>" style="display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:14px;color:var(--text);transition:background .2s" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"><i class="fas fa-tachometer-alt" style="width:16px;color:var(--accent)"></i> Dashboard</a>
              <?php if ($profile): ?><a href="<?= $profile ?>" style="display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:14px;color:var(--text);transition:background .2s" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"><i class="fas fa-user" style="width:16px;color:var(--accent)"></i> My Profile</a><?php endif; ?>
              <?php if ($role === 'student'): ?>
                <a href="<?= BASE_URL ?>/user/notifications.php" style="display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:14px;color:var(--text);transition:background .2s" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"><i class="fas fa-bell" style="width:16px;color:var(--accent)"></i> Notifications <?php if ($unread_count > 0): ?><span class="badge badge-danger" style="font-size:11px;padding:2px 6px"><?= $unread_count ?></span><?php endif; ?></a>
                <a href="<?= BASE_URL ?>/user/payments.php" style="display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:14px;color:var(--text);transition:background .2s" onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background=''"><i class="fas fa-receipt" style="width:16px;color:var(--accent)"></i> Payments</a>
              <?php endif; ?>
              <div style="border-top:1px solid var(--border)">
                <a href="<?= BASE_URL ?>/logout.php" style="display:flex;align-items:center;gap:10px;padding:11px 16px;font-size:14px;color:var(--danger);transition:background .2s" onmouseover="this.style.background='#fff5f5'" onmouseout="this.style.background=''"><i class="fas fa-sign-out-alt" style="width:16px"></i> Logout</a>
              </div>
            </div>
          </div>

        <?php else: ?>
          <a href="<?= BASE_URL ?>/login.php"  class="btn btn-ghost btn-sm">Log In</a>
          <a href="<?= BASE_URL ?>/signup.php" class="btn btn-primary btn-sm">Sign Up Free</a>
        <?php endif; ?>

        <!-- Hamburger -->
        <button class="hamburger" id="hamburger" aria-label="Menu" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </div>
</nav>

<!-- Mobile Nav Drawer -->
<div class="mobile-nav" id="mobile-nav">
  <a href="<?= BASE_URL ?>/index.php"   class="nav-link">🏠 Home</a>
  <a href="<?= BASE_URL ?>/explore.php" class="nav-link">🔍 Explore PGs</a>
  <a href="<?= BASE_URL ?>/about.php"   class="nav-link">ℹ️ About</a>
  <a href="<?= BASE_URL ?>/contact.php" class="nav-link">✉️ Contact</a>
  <?php if ($is_logged): ?>
    <hr style="border:none;border-top:1px solid var(--border);margin:8px 0">
    <?php if ($role === 'student'): ?>
      <a href="<?= BASE_URL ?>/user/dashboard.php"  class="nav-link">📊 Dashboard</a>
      <a href="<?= BASE_URL ?>/user/bookings.php"   class="nav-link">📋 My Bookings</a>
      <a href="<?= BASE_URL ?>/user/saved.php"      class="nav-link">❤️ Saved PGs</a>
      <a href="<?= BASE_URL ?>/user/notifications.php" class="nav-link">🔔 Notifications</a>
    <?php elseif ($role === 'owner'): ?>
      <a href="<?= BASE_URL ?>/owner/dashboard.php" class="nav-link">📊 Dashboard</a>
      <a href="<?= BASE_URL ?>/owner/listings.php"  class="nav-link">🏘️ My Listings</a>
      <a href="<?= BASE_URL ?>/owner/bookings.php"  class="nav-link">📋 Bookings</a>
    <?php elseif ($role === 'admin'): ?>
      <a href="<?= BASE_URL ?>/admin/dashboard.php" class="nav-link">⚙️ Admin Panel</a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/logout.php" class="nav-link" style="color:var(--danger)">🚪 Logout</a>
  <?php else: ?>
    <hr style="border:none;border-top:1px solid var(--border);margin:8px 0">
    <a href="<?= BASE_URL ?>/login.php"  class="btn btn-outline btn-w100 mb-8">Log In</a>
    <a href="<?= BASE_URL ?>/signup.php" class="btn btn-primary btn-w100">Sign Up Free</a>
  <?php endif; ?>
</div>

<script>
// Profile dropdown toggle (inline for immediate availability)
document.getElementById('profile-btn')?.addEventListener('click', function(e) {
  e.stopPropagation();
  const m = document.getElementById('profile-menu');
  m.style.display = m.style.display === 'none' ? 'block' : 'none';
  m.style.animation = 'slideDown .2s';
});
document.addEventListener('click', () => {
  const m = document.getElementById('profile-menu');
  if (m) m.style.display = 'none';
});
</script>
