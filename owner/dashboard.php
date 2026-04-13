<?php
// owner/dashboard.php — Owner Dashboard
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('owner');
$uid = current_user_id();

// Stats
$stats = $pdo->prepare("
  SELECT
    (SELECT COUNT(*) FROM pg_listings WHERE owner_id=? AND is_deleted=0) AS total_listings,
    (SELECT COUNT(*) FROM pg_listings WHERE owner_id=? AND status='approved' AND is_deleted=0) AS active_listings,
    (SELECT COUNT(*) FROM bookings WHERE owner_id=? AND status='pending') AS pending_bookings,
    (SELECT COUNT(*) FROM bookings WHERE owner_id=? AND status='confirmed' AND MONTH(created_at)=MONTH(NOW())) AS confirmed_this_month,
    (SELECT COALESCE(SUM(amount - commission_amount),0) FROM payments p JOIN bookings b ON b.id=p.booking_id WHERE b.owner_id=? AND p.status='success' AND MONTH(p.paid_at)=MONTH(NOW())) AS earnings_this_month
");
$stats->execute([$uid,$uid,$uid,$uid,$uid]); $stats=$stats->fetch();

// Occupancy per listing
$listings = $pdo->prepare("SELECT p.id, p.title, p.status, p.area_name, (SELECT SUM(total_beds) FROM room_types WHERE pg_id=p.id) AS total_beds, (SELECT SUM(available_beds) FROM room_types WHERE pg_id=p.id) AS available_beds FROM pg_listings p WHERE p.owner_id=? AND p.is_deleted=0 ORDER BY p.created_at DESC LIMIT 5");
$listings->execute([$uid]); $my_listings=$listings->fetchAll();

// Recent bookings
$bk = $pdo->prepare("SELECT b.*, p.title AS pg_title, u.name AS student_name, u.phone AS student_phone, rt.type AS room_type FROM bookings b JOIN pg_listings p ON p.id=b.pg_id JOIN users u ON u.id=b.student_id JOIN room_types rt ON rt.id=b.room_type_id WHERE b.owner_id=? ORDER BY b.created_at DESC LIMIT 8");
$bk->execute([$uid]); $bookings=$bk->fetchAll();

// Monthly earnings (last 6 months) for chart
$chart = $pdo->prepare("SELECT DATE_FORMAT(p.paid_at,'%b %Y') AS month, SUM(p.amount-p.commission_amount) AS net FROM payments p JOIN bookings b ON b.id=p.booking_id WHERE b.owner_id=? AND p.status='success' AND p.paid_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY YEAR(p.paid_at),MONTH(p.paid_at) ORDER BY p.paid_at");
$chart->execute([$uid]); $chart_data=$chart->fetchAll();
$chart_labels = array_column($chart_data,'month');
$chart_values = array_column($chart_data,'net');
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Owner Dashboard — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head><body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
  <?php require_once '../components/sidebar.php'; ?>

  <div class="main-content">
    <?php if ($m=flash_get('success')): ?><div class="alert alert-success" data-dismiss="4000"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($m) ?></div><?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
      <div><h2 style="margin-bottom:4px">Owner Dashboard</h2><p style="color:var(--text-muted);margin:0">Manage your PG listings and bookings</p></div>
      <a href="add-listing.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Listing</a>
    </div>

    <!-- Stats -->
    <div class="admin-grid" style="margin-bottom:32px">
      <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-home"></i></div>
        <div><div class="stat-num"><?= $stats['total_listings'] ?></div><div class="stat-label">Total Listings</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div><div class="stat-num"><?= $stats['pending_bookings'] ?></div><div class="stat-label">Pending Requests</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div><div class="stat-num"><?= $stats['confirmed_this_month'] ?></div><div class="stat-label">Confirmed This Month</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-rupee-sign"></i></div>
        <div><div class="stat-num" style="font-size:1.4rem"><?= format_currency_compact((int)$stats['earnings_this_month']) ?></div><div class="stat-label">Earnings This Month</div></div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
      <!-- Occupancy -->
      <div class="card">
        <div class="card-header"><h3 style="margin:0;font-size:16px"><i class="fas fa-bed" style="color:var(--accent)"></i> Occupancy Rate</h3></div>
        <div class="card-body">
          <?php if (empty($my_listings)): ?><p>No listings yet.</p>
          <?php else: foreach($my_listings as $l):
            $filled = max(0, (int)$l['total_beds'] - (int)$l['available_beds']);
            $pct = $l['total_beds'] > 0 ? round($filled/$l['total_beds']*100) : 0;
            $cls = $pct>=70?'high':($pct>=40?'medium':'low');
          ?>
            <div style="margin-bottom:14px">
              <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:5px">
                <span style="font-weight:600;color:var(--primary)"><?= htmlspecialchars(truncate($l['title'],30)) ?></span>
                <span style="color:var(--text-muted)"><?= $filled ?>/<?= $l['total_beds'] ?> beds · <strong><?= $pct ?>%</strong></span>
              </div>
              <div class="occupancy-bar"><div class="occupancy-fill <?= $cls ?>" style="width:<?= $pct ?>%"></div></div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- Earnings Chart -->
      <div class="card">
        <div class="card-header"><h3 style="margin:0;font-size:16px"><i class="fas fa-chart-bar" style="color:var(--accent)"></i> Monthly Earnings</h3></div>
        <div class="card-body">
          <?php if (empty($chart_data)): ?><div class="empty-state" style="padding:30px"><p>No payment data yet.</p></div>
          <?php else: ?><canvas id="earningsChart" height="180"></canvas><?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Recent Bookings -->
    <div class="card">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3 style="margin:0;font-size:16px"><i class="fas fa-calendar-check" style="color:var(--accent)"></i> Recent Booking Requests</h3>
        <a href="bookings.php" class="btn btn-ghost btn-sm">View All →</a>
      </div>
      <?php if (empty($bookings)): ?>
        <div class="empty-state" style="padding:40px"><i class="fas fa-calendar-times"></i><p>No bookings yet.</p></div>
      <?php else: ?>
      <div class="table-wrap" style="border:none;border-radius:0">
        <table>
          <thead><tr><th>Student</th><th>PG / Room</th><th>Move-in</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach($bookings as $b): ?>
            <tr>
              <td><strong><?= htmlspecialchars($b['student_name']) ?></strong></td>
              <td><?= htmlspecialchars(truncate($b['pg_title'],25)) ?><div style="font-size:11px;color:var(--text-muted)"><?= ucfirst($b['room_type']) ?></div></td>
              <td><?= date('d M Y',strtotime($b['move_in_date'])) ?></td>
              <td style="font-weight:700"><?= format_currency($b['total_amount']) ?></td>
              <td><?= status_badge($b['status']) ?></td>
              <td>
                <div style="display:flex;gap:6px">
                  <?php if ($b['status']==='pending'): ?>
                  <form method="POST" action="bookings.php" style="display:inline">
                    <?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                    <button name="action" value="confirm" class="btn btn-success btn-sm">Accept</button>
                    <button name="action" value="reject" class="btn btn-danger btn-sm" onclick="return confirm('Reject this booking?')">Reject</button>
                  </form>
                  <?php endif; ?>
                  <a href="chat.php?with=<?= $b['student_id'] ?>&pg_id=<?= $b['pg_id'] ?>" class="btn btn-ghost btn-sm" title="Chat"><i class="fas fa-comment"></i></a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
<script>
var BASE_URL='<?= BASE_URL ?>';
<?php if (!empty($chart_data)): ?>
new Chart(document.getElementById('earningsChart'), {
  type:'bar',
  data: { labels: <?= json_encode($chart_labels) ?>, datasets:[{ label:'Net Earnings (₹)', data:<?= json_encode($chart_values) ?>, backgroundColor:'rgba(46,134,171,.7)', borderColor:'#2E86AB', borderWidth:2, borderRadius:6 }] },
  options: { responsive:true, plugins:{ legend:{display:false} }, scales:{ y:{ beginAtZero:true, ticks:{ callback:v=>'₹'+v.toLocaleString('en-IN') } } } }
});
<?php endif; ?>
</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
