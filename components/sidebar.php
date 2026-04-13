<?php
// components/sidebar.php — Universal Sidebar for Admin, Owner, and Student
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

$role = $_SESSION['role'] ?? '';
$user_name = $_SESSION['name'] ?? 'User';
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch some stats for badges (Admin only)
$pending_listings = 0;
$open_complaints = 0;
$pending_kyc = 0;
$unread_notifs = 0;

$pdo_side = get_db();
if ($role === 'admin') {
    $pending_listings = (int)$pdo_side->query("SELECT COUNT(*) FROM pg_listings WHERE status='pending' AND is_deleted=0")->fetchColumn();
    $open_complaints = (int)$pdo_side->query("SELECT COUNT(*) FROM complaints WHERE status='open'")->fetchColumn();
}

$unread_notifs = unread_notification_count($pdo_side, (int)($_SESSION['user_id'] ?? 0));
?>
<div class="sidebar">
    <div class="sidebar-logo">
        <?php if ($role === 'admin'): ?>
            <h3>⚙️ Admin Panel</h3><p>MUJSTAYS Operations</p>
        <?php elseif ($role === 'owner'): ?>
            <h3>🏘️ Owner Panel</h3><p><?= htmlspecialchars($user_name) ?></p>
        <?php else: ?>
            <h3>🎓 Student Hub</h3><p><?= htmlspecialchars($user_name) ?></p>
        <?php endif; ?>
    </div>

    <nav class="sidebar-menu">
        <?php if ($role === 'admin'): ?>
            <!-- ADMIN SIDEBAR -->
            <div class="sidebar-section">Overview</div>
            <a href="dashboard.php" class="sidebar-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>

            <div class="sidebar-section">Moderation</div>
            <a href="listings.php" class="sidebar-link <?= $current_page == 'listings.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Listings
                <?php if ($pending_listings > 0): ?><span class="badge badge-warning" style="font-size:10px;margin-left:auto"><?= $pending_listings ?></span><?php endif; ?>
            </a>
            <a href="users.php" class="sidebar-link <?= $current_page == 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="complaints.php" class="sidebar-link <?= $current_page == 'complaints.php' ? 'active' : '' ?>">
                <i class="fas fa-flag"></i> Complaints
                <?php if ($open_complaints > 0): ?><span class="badge badge-danger" style="font-size:10px;margin-left:auto"><?= $open_complaints ?></span><?php endif; ?>
            </a>
            <a href="reviews.php" class="sidebar-link <?= $current_page == 'reviews.php' ? 'active' : '' ?>">
                <i class="fas fa-star"></i> Reviews
            </a>

            <div class="sidebar-section">Finance</div>
            <a href="bookings.php" class="sidebar-link <?= $current_page == 'bookings.php' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i> All Bookings
            </a>
            <a href="payments.php" class="sidebar-link <?= $current_page == 'payments.php' ? 'active' : '' ?>">
                <i class="fas fa-money-bill-wave"></i> Payments
            </a>

            <div class="sidebar-section">Communication</div>
            <a href="notifications.php" class="sidebar-link <?= $current_page == 'notifications.php' ? 'active' : '' ?>">
                <i class="fas fa-bell"></i> Notifications
            </a>

        <?php elseif ($role === 'owner'): ?>
            <!-- OWNER SIDEBAR -->
            <div class="sidebar-section">Business</div>
            <a href="dashboard.php" class="sidebar-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="listings.php" class="sidebar-link <?= $current_page == 'listings.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> My Listings
            </a>
            <a href="add-listing.php" class="sidebar-link <?= $current_page == 'add-listing.php' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i> Add Listing
            </a>

            <div class="sidebar-section">Operations</div>
            <a href="bookings.php" class="sidebar-link <?= $current_page == 'bookings.php' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i> Bookings
            </a>
            <a href="payments.php" class="sidebar-link <?= $current_page == 'payments.php' ? 'active' : '' ?>">
                <i class="fas fa-money-bill-wave"></i> Payments
            </a>

            <div class="sidebar-section">Engagement</div>
            <a href="chat.php" class="sidebar-link <?= $current_page == 'chat.php' ? 'active' : '' ?>">
                <i class="fas fa-comments"></i> Messages
            </a>
            <a href="reviews.php" class="sidebar-link <?= $current_page == 'reviews.php' ? 'active' : '' ?>">
                <i class="fas fa-star"></i> Reviews
            </a>

            <div class="sidebar-section">Settings</div>
            <a href="profile.php" class="sidebar-link <?= $current_page == 'profile.php' ? 'active' : '' ?>">
                <i class="fas fa-user-shield"></i> Profile & KYC
            </a>

        <?php else: ?>
            <!-- STUDENT SIDEBAR -->
            <div class="sidebar-section">Main</div>
            <a href="dashboard.php" class="sidebar-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="bookings.php" class="sidebar-link <?= $current_page == 'bookings.php' ? 'active' : '' ?>">
                <i class="fas fa-calendar-check"></i> My Bookings
            </a>
            <a href="saved.php" class="sidebar-link <?= $current_page == 'saved.php' ? 'active' : '' ?>">
                <i class="fas fa-heart"></i> Saved PGs
            </a>
            <a href="compare.php" class="sidebar-link <?= $current_page == 'compare.php' ? 'active' : '' ?>">
                <i class="fas fa-balance-scale"></i> Compare
            </a>

            <div class="sidebar-section">Activity</div>
            <a href="chat.php" class="sidebar-link <?= $current_page == 'chat.php' ? 'active' : '' ?>">
                <i class="fas fa-comments"></i> Messages
            </a>
            <a href="notifications.php" class="sidebar-link <?= $current_page == 'notifications.php' ? 'active' : '' ?>">
                <i class="fas fa-bell"></i> Notifications
                <?php if ($unread_notifs > 0): ?><span class="badge badge-danger" style="font-size:10px;margin-left:auto"><?= $unread_notifs ?></span><?php endif; ?>
            </a>
            <a href="reviews.php" class="sidebar-link <?= $current_page == 'reviews.php' ? 'active' : '' ?>">
                <i class="fas fa-star"></i> My Reviews
            </a>
            <a href="payments.php" class="sidebar-link <?= $current_page == 'payments.php' ? 'active' : '' ?>">
                <i class="fas fa-receipt"></i> Payments
            </a>

            <div class="sidebar-section">Settings</div>
            <a href="profile.php" class="sidebar-link <?= $current_page == 'profile.php' ? 'active' : '' ?>">
                <i class="fas fa-user"></i> Profile
            </a>
        <?php endif; ?>

        <div class="sidebar-section">System</div>
        <a href="<?= BASE_URL ?>/logout.php" class="sidebar-link" style="color:rgba(255,100,100,.8)">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>
