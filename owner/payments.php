<?php
// owner/payments.php — Payments Received by Owner
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('owner');
$uid = current_user_id();

// Fetch payments for PG listings owned by this user
$stmt = $pdo->prepare("
    SELECT p.*, b.status as booking_status, pg.title as pg_title, u.name as payer_name, u.email as payer_email
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN pg_listings pg ON b.pg_id = pg.id
    JOIN users u ON p.payer_id = u.id
    WHERE pg.owner_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$uid]);
$payments = $stmt->fetchAll();

// Calculate total earnings
$total_earnings = 0;
foreach ($payments as $p) {
    if ($p['status'] === 'captured' || $p['status'] === 'success') {
        $total_earnings += $p['amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Payments Received — MUJSTAYS Owner</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
    <?php require_once '../components/sidebar.php'; ?>
    <div class="main-content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
            <h2 style="margin:0">Payments Received</h2>
            <div class="card" style="padding:10px 20px; background:var(--bg2); border-left:4px solid var(--success)">
                <div style="font-size:12px; color:var(--text-muted); font-weight:600">Total Revenue</div>
                <div style="font-size:18px; font-weight:800; color:var(--primary)"><?= format_currency($total_earnings) ?></div>
            </div>
        </div>

        <?php if ($m=flash_get('success')): ?><div class="alert alert-success" data-dismiss="4000"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($m) ?></div><?php endif; ?>

        <?php if (empty($payments)): ?>
            <div class="empty-state">
                <i class="fas fa-money-bill-wave"></i>
                <h3>No Payments Received</h3>
                <p>When students book your PG and pay the advance, transactions will appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Reference ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($payments as $p): ?>
                        <tr>
                            <td style="white-space:nowrap"><?= date('d M Y', strtotime($p['created_at'])) ?><div style="font-size:11px;color:var(--text-muted)"><?= date('H:i', strtotime($p['created_at'])) ?></div></td>
                            <td>
                                <div class="fw-600"><?= htmlspecialchars($p['payer_name']) ?></div>
                                <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($p['payer_email']) ?></div>
                            </td>
                            <td>
                                <div class="fw-600 color-primary"><?= htmlspecialchars(truncate($p['pg_title'],30)) ?></div>
                            </td>
                            <td class="fw-700"><?= format_currency($p['amount']) ?></td>
                            <td><?= status_badge($p['status']) ?></td>
                            <td class="small text-muted" style="font-family:monospace; font-size:11px">
                                <?= $p['gateway_payment_id'] ?: 'OID-'.substr($p['gateway_order_id'],-8) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../components/footer.php'; ?>
<script>var BASE_URL='<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
