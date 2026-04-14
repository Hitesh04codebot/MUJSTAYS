<?php
// admin/dashboard.php — Admin Dashboard
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('admin');

// Platform stats
$stats_q = $pdo->query("SELECT
  (SELECT COUNT(*) FROM users WHERE role='student') AS total_students,
  (SELECT COUNT(*) FROM users WHERE role='owner') AS total_owners,
  (SELECT COUNT(*) FROM pg_listings WHERE status='approved' AND is_deleted=0) AS active_listings,
  (SELECT COUNT(*) FROM pg_listings WHERE status='pending') AS pending_listings,
  (SELECT COUNT(*) FROM bookings) AS total_bookings,
  (SELECT COALESCE(SUM(commission_amount),0) FROM payments WHERE status='success') AS total_commission,
  (SELECT COUNT(*) FROM kyc_documents WHERE status='pending') AS pending_kyc,
  (SELECT COUNT(*) FROM complaints WHERE status='open') AS open_complaints
"); $stats = $stats_q->fetch();

// Recent activity feed
$activity = $pdo->query("
  (SELECT 'new_user' AS type, CONCAT(name,' registered as ',role) AS desc_text, created_at AS ts, id AS ref_id FROM users ORDER BY created_at DESC LIMIT 5)
  UNION ALL
  (SELECT 'new_listing', CONCAT('New listing submitted: ', title), created_at, id FROM pg_listings WHERE status='pending' ORDER BY created_at DESC LIMIT 5)
  UNION ALL
  (SELECT 'new_booking','New booking request',created_at,id FROM bookings ORDER BY created_at DESC LIMIT 5)
  ORDER BY ts DESC LIMIT 15
")->fetchAll();

// Monthly commission chart
$commission = $pdo->query("SELECT DATE_FORMAT(paid_at,'%b %Y') AS month, SUM(commission_amount) AS commission FROM payments WHERE status='success' AND paid_at >= DATE_SUB(NOW(),INTERVAL 12 MONTH) GROUP BY YEAR(paid_at),MONTH(paid_at) ORDER BY paid_at")->fetchAll();
$c_labels = array_column($commission,'month');
$c_values = array_column($commission,'commission');

// Top PGs this month
$top_pgs = $pdo->query("
    SELECT p.title, a.name AS area_name, COUNT(b.id) AS bookings 
    FROM bookings b 
    JOIN pg_listings p ON p.id = b.pg_id 
    JOIN areas a ON p.area_id = a.id
    WHERE MONTH(b.created_at) = MONTH(NOW()) 
    GROUP BY b.pg_id 
    ORDER BY bookings DESC LIMIT 5
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head><body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
  <?php require_once '../components/sidebar.php'; ?>

  <div class="main-content">
    <div style="margin-bottom:28px"><h2 style="margin-bottom:4px">Admin Dashboard</h2><p style="color:var(--text-muted);margin:0">Platform overview — <?= date('d F Y') ?></p></div>

    <!-- Stats Grid -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px">
      <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-user-graduate"></i></div><div><div class="stat-num"><?= number_format($stats['total_students']) ?></div><div class="stat-label">Students</div></div></div>
      <div class="stat-card"><div class="stat-icon green"><i class="fas fa-user-tie"></i></div><div><div class="stat-num"><?= number_format($stats['total_owners']) ?></div><div class="stat-label">PG Owners</div></div></div>
      <div class="stat-card"><div class="stat-icon navy"><i class="fas fa-home"></i></div><div><div class="stat-num"><?= number_format($stats['active_listings']) ?></div><div class="stat-label">Active Listings</div></div></div>
      <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-calendar"></i></div><div><div class="stat-num"><?= number_format($stats['total_bookings']) ?></div><div class="stat-label">Total Bookings</div></div></div>
      <div class="stat-card"><div class="stat-icon green"><i class="fas fa-rupee-sign"></i></div><div><div class="stat-num" style="font-size:1.3rem"><?= format_currency_compact((int)$stats['total_commission']) ?></div><div class="stat-label">Commission Earned</div></div></div>
      <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-clock"></i></div><div><div class="stat-num"><?= $stats['pending_listings'] ?></div><div class="stat-label">Listings Pending</div></div></div>
      <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-id-card"></i></div><div><div class="stat-num"><?= $stats['pending_kyc'] ?></div><div class="stat-label">Pending KYC</div></div></div>
      <div class="stat-card"><div class="stat-icon red"><i class="fas fa-flag"></i></div><div><div class="stat-num"><?= $stats['open_complaints'] ?></div><div class="stat-label">Open Complaints</div></div></div>
    </div>

    <!-- Pending Actions -->
    <div class="card" style="margin-bottom:24px;border-left:4px solid var(--warning)">
      <div class="card-body">
        <h3 style="margin-bottom:16px;color:var(--warning)"><i class="fas fa-exclamation-triangle"></i> Pending Actions</h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <?php if ($stats['pending_listings']>0): ?><a href="listings.php?status=pending" class="btn btn-warning"><i class="fas fa-home"></i> <?= $stats['pending_listings'] ?> Listing<?= $stats['pending_listings']>1?'s':'' ?> Awaiting Approval</a><?php endif; ?>
          <?php if ($stats['pending_kyc']>0): ?><a href="users.php?filter=kyc_pending" class="btn btn-warning"><i class="fas fa-id-card"></i> <?= $stats['pending_kyc'] ?> KYC Review<?= $stats['pending_kyc']>1?'s':'' ?></a><?php endif; ?>
          <?php if ($stats['open_complaints']>0): ?><a href="complaints.php?status=open" class="btn btn-danger"><i class="fas fa-flag"></i> <?= $stats['open_complaints'] ?> Open Complaint<?= $stats['open_complaints']>1?'s':'' ?></a><?php endif; ?>
          <?php if ($stats['pending_listings']==0 && $stats['pending_kyc']==0 && $stats['open_complaints']==0): ?><span style="color:var(--success);font-weight:600"><i class="fas fa-check-circle"></i> All caught up! No pending actions.</span><?php endif; ?>
        </div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;margin-bottom:24px">
      <!-- Commission Chart -->
      <div class="card">
        <div class="card-header"><h3 style="margin:0;font-size:16px"><i class="fas fa-chart-line" style="color:var(--accent)"></i> Platform Commission (Last 12 Months)</h3></div>
        <div class="card-body">
          <?php if (!empty($commission)): ?><canvas id="commissionChart" height="200"></canvas>
          <?php else: ?><div class="empty-state" style="padding:40px"><p>No payment data yet.</p></div><?php endif; ?>
        </div>
      </div>

      <!-- Top PGs -->
      <div class="card">
        <div class="card-header"><h3 style="margin:0;font-size:16px"><i class="fas fa-trophy" style="color:#F39C12"></i> Top PGs This Month</h3></div>
        <div class="card-body" style="padding:12px 16px">
          <?php if (empty($top_pgs)): ?><p style="color:var(--text-muted)">No bookings this month yet.</p>
          <?php else: foreach($top_pgs as $i=>$pg): ?>
          <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border)">
            <div style="width:28px;height:28px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:700;font-size:12px;flex-shrink:0"><?= $i+1 ?></div>
            <div style="flex:1;min-width:0"><div style="font-weight:600;font-size:13px;color:var(--primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($pg['title']) ?></div>
            <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($pg['area_name']) ?></div></div>
            <span class="badge badge-success"><?= $pg['bookings'] ?> bk</span>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
<script>
<?php if (!empty($commission)): ?>
new Chart(document.getElementById('commissionChart'), {
  type:'line',
  data:{ labels:<?= json_encode($c_labels) ?>, datasets:[{label:'Commission (₹)', data:<?= json_encode($c_values) ?>, backgroundColor:'rgba(46,134,171,.15)', borderColor:'#2E86AB', borderWidth:2, tension:.4, fill:true, pointBackgroundColor:'#2E86AB'}] },
  options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{callback:v=>'₹'+v.toLocaleString('en-IN')}}} }
});
<?php endif; ?>
</script>
</body></html>
