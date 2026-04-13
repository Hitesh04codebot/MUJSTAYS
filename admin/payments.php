<?php
// admin/payments.php — Platform Payment & Commission Ledger
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('admin');

$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where = "WHERE 1=1";
$params = [];

if (!empty($_GET['status'])) {
    $where .= " AND p.status = ?";
    $params[] = $_GET['status'];
}

if (!empty($_GET['q'])) {
    $where .= " AND (p.gateway_payment_id LIKE ? OR p.gateway_order_id LIKE ? OR u.name LIKE ?)";
    $q = "%" . $_GET['q'] . "%";
    $params[] = $q; $params[] = $q; $params[] = $q;
}

// Total count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM payments p JOIN users u ON p.payer_id = u.id $where");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Fetch payments
$stmt = $pdo->prepare("
    SELECT p.*, u.name as payer_name, u.email as payer_email, b.pg_id, b.owner_id, pg.title as pg_title
    FROM payments p
    JOIN users u ON p.payer_id = u.id
    JOIN bookings b ON p.booking_id = b.id
    JOIN pg_listings pg ON b.pg_id = pg.id
    $where
    ORDER BY p.created_at DESC
    LIMIT $offset, $per_page
");
$stmt->execute($params);
$payments = $stmt->fetchAll();

// Summary stats
$summary = $pdo->query("SELECT 
    COALESCE(SUM(CASE WHEN status='success' THEN amount ELSE 0 END), 0) as total_collected,
    COALESCE(SUM(CASE WHEN status='success' THEN commission_amount ELSE 0 END), 0) as total_commission,
    COUNT(CASE WHEN status='failed' THEN 1 END) as total_failed
    FROM payments
")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Payments & Commissions — MUJSTAYS Admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
    <?php require_once '../components/sidebar.php'; ?>

    <div class="main-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div><h2 style="margin-bottom:4px">Payments & Commissions</h2><p style="color:var(--text-muted);margin:0">Platform revenue and transaction logs.</p></div>
            <div style="display:flex; gap:12px;">
                <div class="stat-card" style="padding:12px 20px; box-shadow:none; border:1px solid var(--border);">
                    <div><div class="stat-num" style="font-size:1.2rem; color:var(--success)">₹<?= number_format($summary['total_commission']) ?></div><div class="stat-label">Total Commission</div></div>
                </div>
            </div>
        </div>

        <div class="card mb-24">
            <div class="card-body" style="padding:16px">
                <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap">
                    <input type="text" name="q" class="form-control" placeholder="Search ID or Payer name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="max-width:300px">
                    <select name="status" class="form-select" style="max-width:160px">
                        <option value="">All Statuses</option>
                        <option value="success" <?= ($_GET['status']??'') === 'success' ? 'selected':'' ?>>Success</option>
                        <option value="failed" <?= ($_GET['status']??'') === 'failed' ? 'selected':'' ?>>Failed</option>
                        <option value="initiated" <?= ($_GET['status']??'') === 'initiated' ? 'selected':'' ?>>Initiated</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php if(!empty($_GET)): ?><a href="payments.php" class="btn btn-secondary">Clear</a><?php endif; ?>
                </form>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Payer</th>
                        <th>Property</th>
                        <th>Amount</th>
                        <th>Commission (<?= COMMISSION_RATE ?>%)</th>
                        <th>Status</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="8" class="text-center py-24">No payment records found.</td></tr>
                    <?php else: foreach($payments as $p): ?>
                        <tr>
                            <td class="small"><?= date('d M, Y H:i', strtotime($p['created_at'])) ?></td>
                            <td style="font-family:monospace; font-size:12px">
                                <div style="color:var(--primary); font-weight:700"><?= $p['gateway_payment_id'] ?: 'N/A' ?></div>
                                <div style="font-size:10px; color:var(--text-muted)">Order: <?= $p['gateway_order_id'] ?></div>
                            </td>
                            <td>
                                <div style="font-weight:600"><?= htmlspecialchars($p['payer_name']) ?></div>
                                <div class="small text-muted"><?= $p['payer_email'] ?></div>
                            </td>
                            <td>
                                <div class="small fw-600"><?= htmlspecialchars(truncate($p['pg_title'], 40)) ?></div>
                            </td>
                            <td class="fw-700"><?= format_currency($p['amount']) ?></td>
                            <td class="fw-700 text-accent"><?= format_currency($p['commission_amount']) ?></td>
                            <td><?= status_badge($p['status']) ?></td>
                            <td class="small text-muted"><?= ucfirst($p['method'] ?: 'razorpay') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination mt-24">
            <?php for($i=1; $i<=$total_pages; $i++): ?>
                <a href="?page=<?= $i ?>&status=<?= $_GET['status']??'' ?>&q=<?= $_GET['q']??'' ?>" class="page-btn <?= $i==$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once '../components/footer.php'; ?>
</body>
</html>
