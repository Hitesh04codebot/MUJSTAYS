<?php
// admin/complaints.php — Admin Complaint Moderation
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verify_csrf()) {
        flash_set('error', 'Invalid request.');
    } else {
        $id = (int)$_POST['id'];
        $action = $_POST['action'];
        $note = sanitize($_POST['note'] ?? '');
        
        switch ($action) {
            case 'resolve':
                $pdo->prepare("UPDATE complaints SET status = 'resolved', admin_note = ?, resolved_at = NOW() WHERE id = ?")->execute([$note, $id]);
                flash_set('success', 'Complaint marked as resolved.');
                break;
            case 'in_review':
                $pdo->prepare("UPDATE complaints SET status = 'in_review', admin_note = ? WHERE id = ?")->execute([$note, $id]);
                flash_set('success', 'Complaint status updated to In Review.');
                break;
            case 'close':
                $pdo->prepare("UPDATE complaints SET status = 'closed', admin_note = ?, resolved_at = NOW() WHERE id = ?")->execute([$note, $id]);
                flash_set('success', 'Complaint closed.');
                break;
        }
        redirect('complaints.php');
    }
}

$status = $_GET['status'] ?? 'open';
$stmt = $pdo->prepare("
    SELECT c.*, u.name as reporter_name, u.email as reporter_email, ru.name as reported_user_name, pg.title as pg_title
    FROM complaints c
    JOIN users u ON c.reporter_id = u.id
    LEFT JOIN users ru ON c.reported_user_id = ru.id
    LEFT JOIN pg_listings pg ON c.pg_id = pg.id
    WHERE c.status = ?
    ORDER BY c.created_at DESC
");
$stmt->execute([$status]);
$complaints = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Complaints & Disputes — MUJSTAYS Admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
    <?php require_once '../components/sidebar.php'; ?>

    <div class="main-content">
        <h2 class="mb-24">Complaints & Disputes</h2>
        
        <div class="tabs">
            <a href="?status=open" class="tab-btn <?= $status=='open'?'active':'' ?>">Open</a>
            <a href="?status=in_review" class="tab-btn <?= $status=='in_review'?'active':'' ?>">In Review</a>
            <a href="?status=resolved" class="tab-btn <?= $status=='resolved'?'active':'' ?>">Resolved</a>
            <a href="?status=closed" class="tab-btn <?= $status=='closed'?'active':'' ?>">Closed</a>
        </div>

        <?php if (flash_has('success')): ?><div class="alert alert-success"><?= flash_get('success') ?></div><?php endif; ?>
        <?php if (flash_has('error')): ?><div class="alert alert-error"><?= flash_get('error') ?></div><?php endif; ?>

        <?php if (empty($complaints)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle" style="color:var(--success); opacity:1"></i>
                <h3>No Complaints Found</h3>
                <p>Everything looks clean in this category.</p>
            </div>
        <?php else: foreach($complaints as $c): ?>
            <div class="card mb-24">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center">
                    <div style="font-weight:700; color:var(--primary)">
                        <span class="badge badge-primary mr-8"><?= ucfirst($c['type'] ?: 'Issue') ?></span>
                        Complaint ID #<?= $c['id'] ?>
                    </div>
                    <span class="small text-muted"><?= time_ago($c['created_at']) ?></span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div style="flex:2">
                            <p class="fw-700 mb-8"><?= htmlspecialchars($c['description']) ?></p>
                            <div style="font-size:13px; color:var(--text-muted)">
                                Reporter: <strong><?= htmlspecialchars($c['reporter_name']) ?></strong> (<?= $c['reporter_email'] ?>)<br>
                                <?php if($c['reported_user_name']): ?>Reported Owner: <strong><?= htmlspecialchars($c['reported_user_name']) ?></strong><br><?php endif; ?>
                                <?php if($c['pg_title']): ?>Property: <strong><?= htmlspecialchars($c['pg_title']) ?></strong><?php endif; ?>
                            </div>
                        </div>
                        <div style="flex:1; border-left:1px solid var(--border); padding-left:24px">
                            <form method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <div class="form-group mb-12">
                                    <label class="form-label small">Admin Note / Response</label>
                                    <textarea name="note" class="form-control small" style="min-height:60px" placeholder="Internal notes or response to reporter..."><?= htmlspecialchars($c['admin_note'] ?: '') ?></textarea>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px">
                                    <?php if ($status == 'open'): ?>
                                        <button type="submit" name="action" value="in_review" class="btn btn-outline btn-sm">Move to Review</button>
                                    <?php endif; ?>
                                    <?php if ($status != 'resolved'): ?>
                                        <button type="submit" name="action" value="resolve" class="btn btn-success btn-sm">Resolve Now</button>
                                    <?php endif; ?>
                                    <button type="submit" name="action" value="close" class="btn btn-secondary btn-sm">Close Thread</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
<?php require_once '../components/footer.php'; ?>
</body>
</html>
