<?php
// owner/reviews.php — Owner Reviews Management
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('owner');
$uid = current_user_id();
$error = ''; $success = '';

// Handle response submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_response'])) {
    if (!verify_csrf()) {
        $error = 'Invalid request.';
    } else {
        $review_id = (int)$_POST['review_id'];
        $response = sanitize($_POST['owner_response'] ?? '');

        // Verify that this owner owns the PG for this review
        $check = $pdo->prepare("
            SELECT r.id 
            FROM reviews r 
            JOIN pg_listings p ON r.pg_id = p.id 
            WHERE r.id = ? AND p.owner_id = ?
        ");
        $check->execute([$review_id, $uid]);
        if (!$check->fetch()) {
            $error = 'You do not have permission to respond to this review.';
        } else {
            $stmt = $pdo->prepare("UPDATE reviews SET owner_response = ? WHERE id = ?");
            if ($stmt->execute([$response, $review_id])) {
                $success = 'Response saved successfully.';
            } else {
                $error = 'Failed to save response.';
            }
        }
    }
}

// Fetch all reviews for this owner's listings
$stmt = $pdo->prepare("
    SELECT r.*, p.title as pg_title, u.name as student_name, u.profile_photo
    FROM reviews r 
    JOIN pg_listings p ON r.pg_id = p.id 
    JOIN users u ON r.student_id = u.id
    WHERE p.owner_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$uid]);
$reviews = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Manage Reviews — MUJSTAYS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
    <?php require_once '../components/sidebar.php'; ?>
    <div class="main-content">
        <h2 class="mb-24">Guest Reviews</h2>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <?php if (empty($reviews)): ?>
            <div class="empty-state">
                <i class="fas fa-star"></i>
                <h3>No Reviews Yet</h3>
                <p>Reviews will appear here once students complete their stay and rate your PG.</p>
            </div>
        <?php else: foreach($reviews as $r): ?>
            <div class="card mb-16">
                <div class="card-body">
                    <div style="display:flex; justify-content:space-between; margin-bottom:12px">
                        <div style="display:flex; gap:12px; align-items:center">
                            <div style="width:40px; height:40px; background:#eee; border-radius:50%; display:grid; place-items:center; font-weight:700">
                                <?= strtoupper(substr($r['student_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-700"><?= htmlspecialchars($r['student_name']) ?></div>
                                <div class="small text-muted">Reviewed <span class="fw-600"><?= htmlspecialchars($r['pg_title']) ?></span></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="stars mb-4">
                                <?php for($i=1;$i<=5;$i++) echo '<i class="' . ($i<=$r['rating']?'fas':'far') . ' fa-star"></i>'; ?>
                            </div>
                            <div class="small text-muted"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
                        </div>
                    </div>
                    
                    <p class="mb-16" style="padding-left:52px"><?= nl2br(htmlspecialchars($r['review_text'])) ?></p>
                    
                    <div style="padding-left:52px">
                        <?php if($r['owner_response']): ?>
                            <div style="padding:12px; background:var(--bg2); border-radius:8px; border-left:3px solid var(--accent)">
                                <div class="small fw-700 mb-4">Your Response:</div>
                                <div class="small text-muted mb-8"><?= htmlspecialchars($r['owner_response']) ?></div>
                                <button class="btn btn-ghost btn-xs" onclick="toggleResponseForm(<?= $r['id'] ?>)">Edit Response</button>
                            </div>
                        <?php else: ?>
                            <button class="btn btn-outline btn-sm" onclick="toggleResponseForm(<?= $r['id'] ?>)"><i class="fas fa-reply"></i> Reply to Review</button>
                        <?php endif; ?>

                        <div id="response-form-<?= $r['id'] ?>" style="display:none; margin-top:12px">
                            <form method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                                <div class="form-group mb-8">
                                    <textarea name="owner_response" class="form-control" placeholder="Write your response..." required><?= htmlspecialchars($r['owner_response'] ?? '') ?></textarea>
                                </div>
                                <div style="display:flex; gap:8px">
                                    <button type="submit" name="submit_response" class="btn btn-primary btn-sm">Save Response</button>
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="toggleResponseForm(<?= $r['id'] ?>)">Cancel</button>
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
<script>
function toggleResponseForm(id) {
    const el = document.getElementById('response-form-' + id);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}
</script>
</body>
</html>
