<?php
// admin/reviews.php — Admin Reviews Management
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('admin');
$error = ''; $success = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid request.';
    } else {
        $id = (int)$_POST['id'];
        $action = $_POST['action'] ?? '';

        if ($action === 'delete') {
            $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
            $success = 'Review deleted successfully.';
        } elseif ($action === 'toggle_visibility') {
            $pdo->prepare("UPDATE reviews SET is_approved = NOT is_approved WHERE id = ?")->execute([$id]);
            $success = 'Visibility toggled.';
        } elseif ($action === 'toggle_pin') {
            $pdo->prepare("UPDATE reviews SET is_pinned = NOT is_pinned WHERE id = ?")->execute([$id]);
            $success = 'Pin status updated.';
        }
    }
}

// Fetch all reviews with PG and student details
$stmt = $pdo->query("
    SELECT r.*, p.title as pg_title, u.name as student_name 
    FROM reviews r 
    JOIN pg_listings p ON r.pg_id = p.id 
    JOIN users u ON r.student_id = u.id 
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manage Reviews — Admin Panel</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
    <?php require_once '../components/sidebar.php'; ?>
    <div class="main-content">
        <h2 class="mb-24">Platform Reviews</h2>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>PG</th>
                            <th>Review</th>
                            <th>Rating</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($reviews as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['student_name']) ?></strong></td>
                            <td><?= htmlspecialchars($r['pg_title']) ?></td>
                            <td>
                                <div style="max-width:300px; font-size:13px; color:var(--text-muted)">
                                    <?= htmlspecialchars($r['review_text']) ?>
                                </div>
                                <?php if($r['owner_response']): ?>
                                    <div style="font-size:11px; margin-top:4px; color:var(--accent)">
                                        <strong>Resp:</strong> <?= htmlspecialchars($r['owner_response']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="stars" style="color:#F39C12; font-size:12px">
                                    <?php for($i=1;$i<=5;$i++) echo '<i class="' . ($i<=$r['rating']?'fas':'far') . ' fa-star"></i>'; ?>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex; gap:4px">
                                    <?php if($r['is_approved']): ?>
                                        <span class="badge badge-success">Visible</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Hidden</span>
                                    <?php endif; ?>
                                    <?php if($r['is_pinned']): ?>
                                        <span class="badge badge-primary"><i class="fas fa-thumbtack"></i> Pinned</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="display:flex; gap:8px">
                                    <form method="POST" style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        
                                        <button name="action" value="toggle_pin" class="btn btn-ghost btn-xs" title="<?= $r['is_pinned']?'Unpin':'Pin to Homepage' ?>">
                                            <i class="fas fa-thumbtack" style="<?= $r['is_pinned']?'color:var(--primary)':'' ?>"></i>
                                        </button>
                                        
                                        <button name="action" value="toggle_visibility" class="btn btn-ghost btn-xs" title="<?= $r['is_approved']?'Hide':'Show' ?>">
                                            <i class="<?= $r['is_approved']?'fas fa-eye-slash':'fas fa-eye' ?>"></i>
                                        </button>
                                        
                                        <button name="action" value="delete" class="btn btn-ghost btn-xs text-danger" onclick="return confirm('Delete this review forever?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once '../components/footer.php'; ?>
</body>
</html>
