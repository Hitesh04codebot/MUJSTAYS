<?php
// admin/notifications.php — System Broadcast & Notification Management
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('admin');

// Handle broadcast form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $target = $_POST['target'] ?? 'all';
    $title  = sanitize($_POST['title'] ?? '');
    $body   = sanitize($_POST['body'] ?? '');
    $link   = sanitize($_POST['link'] ?? '');
    $type   = $_POST['type'] ?? 'info';

    if ($title && $body) {
        $where = "1=1";
        $params = [];
        if ($target === 'students') { $where = "role = 'student'"; }
        elseif ($target === 'owners') { $where = "role = 'owner'"; }

        // Fetch user IDs
        $users = $pdo->query("SELECT id FROM users WHERE $where")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!empty($users)) {
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, body, link) VALUES (?, ?, ?, ?, ?)");
            foreach ($users as $uid) {
                $stmt->execute([$uid, $type, $title, $body, $link]);
            }
            flash_set('success', 'Broadcast sent successfully to ' . count($users) . ' users.');
        } else {
            flash_set('error', 'No users found for the selected target.');
        }
    } else {
        flash_set('error', 'Please fill in both title and message.');
    }
    header('Location: notifications.php'); exit;
}

// Fetch recent broadcasts (grouped by unique title/body/created_at to show as batch)
$stmt = $pdo->query("SELECT title, body, created_at, type, COUNT(*) as recipient_count FROM notifications GROUP BY title, body, created_at, type ORDER BY created_at DESC LIMIT 10");
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Notifications — Admin Panel</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
    <?php require_once '../components/sidebar.php'; ?>

    <div class="main-content">
        <?php if ($m=flash_get('success')): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($m) ?></div><?php endif; ?>
        <?php if ($m=flash_get('error')): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($m) ?></div><?php endif; ?>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
            <div><h2 style="margin:0">System Notifications</h2><p style="color:var(--text-muted);margin:0">Send broadcast announcements to platform users.</p></div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
            <!-- Send Broadcast -->
            <div class="card">
                <div class="card-header"><h3 style="margin:0;font-size:16px"><i class="fas fa-paper-plane" style="color:var(--accent)"></i> Send New Broadcast</h3></div>
                <div class="card-body">
                    <form method="POST">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label class="form-label">Target Audience</label>
                            <select name="target" class="form-select">
                                <option value="all">All Users</option>
                                <option value="students">Students Only</option>
                                <option value="owners">PG Owners Only</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alert Type</label>
                            <select name="type" class="form-select">
                                <option value="info">Information (Blue)</option>
                                <option value="success">Announcement (Green)</option>
                                <option value="warning">Maintenance/Warning (Yellow)</option>
                                <option value="danger">Urgent Alert (Red)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" placeholder="Notification headline..." required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Message</label>
                            <textarea name="body" class="form-control" rows="4" placeholder="Type your announcement here..." required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Action Link (Optional)</label>
                            <input type="text" name="link" class="form-control" placeholder="/explore.php or external URL">
                        </div>
                        <button type="submit" class="btn btn-primary btn-w100"><i class="fas fa-bullhorn"></i> Send Broadcast</button>
                    </form>
                </div>
            </div>

            <!-- Broadcast History -->
            <div class="card">
                <div class="card-header"><h3 style="margin:0;font-size:16px"><i class="fas fa-history" style="color:var(--accent)"></i> Recent Broadcasts</h3></div>
                <div class="card-body" style="padding:0; max-height: 600px; overflow-y: auto;">
                    <?php if (empty($history)): ?>
                        <div style="padding:40px;text-align:center;color:var(--text-muted)">
                            <i class="fas fa-bell-slash" style="font-size:24px;margin-bottom:10px"></i>
                            <p>No broadcast history found.</p>
                        </div>
                    <?php else: foreach($history as $h): ?>
                        <div style="padding:16px;border-bottom:1px solid var(--border)">
                            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
                                <span class="badge badge-<?= $h['type'] ?>" style="font-size:10px"><?= strtoupper($h['type']) ?></span>
                                <span style="font-size:11px;color:var(--text-muted)"><?= time_ago($h['created_at']) ?></span>
                            </div>
                            <div style="font-weight:700;font-size:14px;color:var(--primary);margin-bottom:4px"><?= htmlspecialchars($h['title']) ?></div>
                            <div style="font-size:12px;color:var(--text);line-height:1.5"><?= htmlspecialchars(truncate($h['body'], 100)) ?></div>
                            <div style="margin-top:8px;font-size:11px;font-weight:600;color:var(--accent)">
                                <i class="fas fa-users"></i> Sent to <?= $h['recipient_count'] ?> users
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../components/footer.php'; ?>
</body>
</html>
