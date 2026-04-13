<?php
// user/payments.php — Student Payment History
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('student');
$uid = current_user_id();

$stmt = $pdo->prepare("
    SELECT p.*, b.status as booking_status, pg.title as pg_title
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN pg_listings pg ON b.pg_id = pg.id
    WHERE p.payer_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$uid]);
$payments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>My Payments — MUJSTAYS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
    <?php require_once '../components/sidebar.php'; ?>
    <div class="main-content">
        <h2 class="mb-24">My Payments</h2>

        <?php if (empty($payments)): ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <h3>No Payments Found</h3>
                <p>You haven't made any transactions on MUJSTAYS yet.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Reference ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($payments as $p): ?>
                        <tr>
                            <td><?= date('d M Y, H:i', strtotime($p['created_at'])) ?></td>
                            <td class="fw-600 color-primary"><?= htmlspecialchars($p['pg_title']) ?></td>
                            <td class="fw-700"><?= format_currency($p['amount']) ?></td>
                            <td><?= status_badge($p['status']) ?></td>
                            <td class="small text-muted" style="font-family:monospace"><?= $p['gateway_payment_id'] ?: 'Pending-'.substr($p['gateway_order_id'],-8) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../components/footer.php'; ?>
</body>
</html>
