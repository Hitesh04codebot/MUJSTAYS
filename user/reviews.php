<?php
// user/reviews.php — Student Reviews Management
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('student');
$uid = current_user_id();
$error = ''; $success = '';

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!verify_csrf()) {
        $error = 'Invalid request.';
    } else {
        $pg_id = (int)$_POST['pg_id'];
        $booking_id = (int)$_POST['booking_id'];
        $rating = (int)$_POST['rating'];
        $comment = sanitize($_POST['review_text'] ?? '');

        // Verify booking status
        $check = $pdo->prepare("SELECT id FROM bookings WHERE id = ? AND student_id = ? AND status = 'completed'");
        $check->execute([$booking_id, $uid]);
        if (!$check->fetch()) {
            $error = 'You can only review completed stays.';
        } elseif ($rating < 1 || $rating > 5) {
            $error = 'Please provide a valid rating.';
        } else {
            // Check if already reviewed
            $check2 = $pdo->prepare("SELECT id FROM reviews WHERE booking_id = ?");
            $check2->execute([$booking_id]);
            if ($check2->fetch()) {
                $error = 'You have already reviewed this stay.';
            } else {
                $pdo->prepare("INSERT INTO reviews (pg_id, student_id, booking_id, rating, review_text) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$pg_id, $uid, $booking_id, $rating, $comment]);
                
                // Update PG average rating
                $pdo->prepare("UPDATE pg_listings SET avg_rating = (SELECT AVG(rating) FROM reviews WHERE pg_id = ?), total_reviews = (SELECT COUNT(*) FROM reviews WHERE pg_id = ?) WHERE id = ?")
                    ->execute([$pg_id, $pg_id, $pg_id]);
                
                $success = 'Review posted successfully!';
            }
        }
    }
}

// Fetch existing reviews by the student
$stmt = $pdo->prepare("
    SELECT r.*, p.title as pg_title, p.id as pg_id 
    FROM reviews r 
    JOIN pg_listings p ON r.pg_id = p.id 
    WHERE r.student_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$uid]);
$reviews = $stmt->fetchAll();

// If coming from bookings page with a specific booking to review
$booking_to_review = null;
if (isset($_GET['booking_id'])) {
    $stmt = $pdo->prepare("
        SELECT b.*, p.title as pg_title 
        FROM bookings b 
        JOIN pg_listings p ON b.pg_id = p.id 
        WHERE b.id = ? AND b.student_id = ? AND b.status = 'completed'
    ");
    $stmt->execute([(int)$_GET['booking_id'], $uid]);
    $booking_to_review = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>My Reviews — MUJSTAYS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
    <div class="sidebar">
        <div class="sidebar-logo"><h3>🎓 Student</h3><p><?= htmlspecialchars($_SESSION['name']) ?></p></div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="bookings.php"  class="sidebar-link"><i class="fas fa-calendar-check"></i> My Bookings</a>
            <a href="saved.php"     class="sidebar-link"><i class="fas fa-heart"></i> Saved PGs</a>
            <a href="compare.php"   class="sidebar-link"><i class="fas fa-balance-scale"></i> Compare</a>
            <a href="chat.php"      class="sidebar-link"><i class="fas fa-comments"></i> Messages</a>
            <a href="notifications.php" class="sidebar-link"><i class="fas fa-bell"></i> Notifications</a>
            <a href="reviews.php"   class="sidebar-link active"><i class="fas fa-star"></i> My Reviews</a>
            <a href="payments.php"  class="sidebar-link"><i class="fas fa-receipt"></i> Payments</a>
            <a href="profile.php"   class="sidebar-link"><i class="fas fa-user"></i> Profile</a>
            <a href="<?= BASE_URL ?>/logout.php" class="sidebar-link" style="color:rgba(255,100,100,.8)"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>
    <div class="main-content">
        <h2 class="mb-24">My Reviews</h2>

        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <?php if ($booking_to_review): ?>
            <div class="card mb-24" style="border-left: 4px solid var(--accent);">
                <div class="card-header"><h3>Post Review for <?= htmlspecialchars($booking_to_review['pg_title']) ?></h3></div>
                <div class="card-body">
                    <form method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="pg_id" value="<?= $booking_to_review['pg_id'] ?>">
                        <input type="hidden" name="booking_id" value="<?= $booking_to_review['id'] ?>">
                        
                        <div class="form-group">
                            <label class="form-label">Rating</label>
                            <div class="stars-input">
                                <?php for($i=5; $i>=1; $i--): ?>
                                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                                    <label for="star<?= $i ?>"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Your Experience</label>
                            <textarea name="review_text" class="form-control" placeholder="Tell others about the food, amenities, and owner behaviour..." required></textarea>
                        </div>
                        <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <div class="empty-state">
                <i class="fas fa-star"></i>
                <h3>No Reviews Yet</h3>
                <p>Complete a stay and share your experience with other MUJ students.</p>
            </div>
        <?php else: foreach($reviews as $r): ?>
            <div class="card mb-16">
                <div class="card-body">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px">
                        <div class="fw-700 color-primary"><?= htmlspecialchars($r['pg_title']) ?></div>
                        <div class="small text-muted"><?= date('d M Y', strtotime($r['created_at'])) ?></div>
                    </div>
                    <div class="stars mb-8">
                        <?php for($i=1;$i<=5;$i++) echo '<i class="' . ($i<=$r['rating']?'fas':'far') . ' fa-star"></i>'; ?>
                    </div>
                    <p class="mb-12"><?= nl2br(htmlspecialchars($r['review_text'])) ?></p>
                    
                    <?php if($r['owner_response']): ?>
                        <div style="padding:12px; background:var(--bg2); border-radius:8px; border-left:3px solid var(--accent); margin-top:12px">
                            <div class="small fw-700 mb-4">Owner Response:</div>
                            <div class="small text-muted"><?= htmlspecialchars($r['owner_response']) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
<?php require_once '../components/footer.php'; ?>
</body>
</html>
