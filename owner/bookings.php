<?php
// owner/bookings.php — Owner Booking Management
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('owner');
$uid = current_user_id();

// Handle accept/reject
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $bid    = (int)($_POST['booking_id']??0);
    $action = $_POST['action']??'';
    $reason = sanitize($_POST['reason']??'');
    $check = $pdo->prepare("SELECT b.*, rt.id AS rtid FROM bookings b JOIN room_types rt ON rt.id=b.room_type_id WHERE b.id=? AND b.owner_id=? AND b.status='pending'");
    $check->execute([$bid,$uid]); $bk=$check->fetch();
    if ($bk) {
        if ($action==='confirm') {
            $pdo->prepare("UPDATE bookings SET status='confirmed',updated_at=NOW() WHERE id=?")->execute([$bid]);
            $pdo->prepare("UPDATE room_types SET available_beds=GREATEST(0,available_beds-1) WHERE id=?")->execute([$bk['rtid']]);
            create_notification($pdo,$bk['student_id'],'booking_update','Booking Confirmed! 🎉',"Your booking has been confirmed by the owner.",'/user/bookings.php');
            flash_set('success','Booking confirmed! The student has been notified.');
        } elseif ($action==='reject') {
            $pdo->prepare("UPDATE bookings SET status='rejected',rejection_reason=?,updated_at=NOW() WHERE id=?")->execute([$reason,$bid]);
            create_notification($pdo,$bk['student_id'],'booking_update','Booking Update','Your booking request was not accepted. Reason: '.$reason,'/user/bookings.php');
            flash_set('success','Booking rejected.');
        }
    }
    header('Location: bookings.php'); exit;
}

$filter = $_GET['filter']??'all';
$where  = "b.owner_id=?"; $params=[$uid];
if (in_array($filter,['pending','confirmed','rejected','cancelled','completed'])) { $where.=" AND b.status=?"; $params[]=$filter; }
$total=$pdo->prepare("SELECT COUNT(*) FROM bookings b WHERE $where"); $total->execute($params); $total=(int)$total->fetchColumn();
$pag=paginate($total,12);
$bk=$pdo->prepare("SELECT b.*, p.title AS pg_title, p.area_name, p.id AS pg_id, rt.type AS room_type, rt.price_per_month, u.name AS student_name, u.phone AS student_phone, u.email AS student_email FROM bookings b JOIN pg_listings p ON p.id=b.pg_id JOIN room_types rt ON rt.id=b.room_type_id JOIN users u ON u.id=b.student_id WHERE $where ORDER BY FIELD(b.status,'pending','confirmed','rejected','cancelled','completed'), b.created_at DESC LIMIT {$pag['per_page']} OFFSET {$pag['offset']}");
$bk->execute($params); $bookings=$bk->fetchAll();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Bookings — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
  <div class="sidebar">
    <div class="sidebar-logo"><h3>🏘️ Owner</h3><p><?= htmlspecialchars($_SESSION['name']) ?></p></div>
    <nav class="sidebar-menu">
      <a href="dashboard.php"   class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="listings.php"    class="sidebar-link"><i class="fas fa-home"></i> My Listings</a>
      <a href="add-listing.php" class="sidebar-link"><i class="fas fa-plus-circle"></i> Add Listing</a>
      <a href="bookings.php"    class="sidebar-link active"><i class="fas fa-calendar-check"></i> Bookings</a>
      <a href="payments.php"    class="sidebar-link"><i class="fas fa-money-bill-wave"></i> Payments</a>
      <a href="chat.php"        class="sidebar-link"><i class="fas fa-comments"></i> Messages</a>
      <a href="reviews.php"     class="sidebar-link"><i class="fas fa-star"></i> Reviews</a>
      <a href="profile.php"     class="sidebar-link"><i class="fas fa-user"></i> Profile & KYC</a>
      <a href="<?= BASE_URL ?>/logout.php" class="sidebar-link" style="color:rgba(255,100,100,.8)"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </div>
  <div class="main-content">
    <?php if ($m=flash_get('success')): ?><div class="alert alert-success" data-dismiss="4000"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($m) ?></div><?php endif; ?>
    <div style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:center">
      <h2 style="margin:0">Booking Requests</h2>
      <span style="font-size:14px;color:var(--text-muted)"><?= $total ?> total booking<?= $total!==1?'s':'' ?></span>
    </div>
    <div class="tabs" style="margin-bottom:24px">
      <?php foreach(['all'=>'All','pending'=>'⏳ Pending','confirmed'=>'✅ Confirmed','rejected'=>'❌ Rejected','completed'=>'🏁 Completed'] as $k=>$v): ?>
        <button class="tab-btn <?= $filter===$k?'active':'' ?>" onclick="window.location.href='?filter=<?= $k ?>'"> <?= $v ?></button>
      <?php endforeach; ?>
    </div>
    <?php if (empty($bookings)): ?>
      <div class="empty-state"><i class="fas fa-calendar-times"></i><h3>No Bookings Found</h3><p>No bookings match your current filter.</p></div>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:16px">
        <?php foreach($bookings as $b): ?>
        <div class="card">
          <div class="card-body" style="display:flex;gap:20px;flex-wrap:wrap">
            <div style="flex:1;min-width:200px">
              <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                <div style="width:40px;height:40px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:700;flex-shrink:0">
                  <?= strtoupper(mb_substr($b['student_name'],0,1)) ?>
                </div>
                <div>
                  <div style="font-weight:700"><?= htmlspecialchars($b['student_name']) ?></div>
                  <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($b['student_email']) ?></div>
                </div>
              </div>
              <div style="font-size:13px;color:var(--text-muted);margin-bottom:4px"><i class="fas fa-home" style="color:var(--accent)"></i> <?= htmlspecialchars(truncate($b['pg_title'],30)) ?> · <?= ucfirst($b['room_type']) ?></div>
              <div style="font-size:13px;color:var(--text-muted)"><i class="fas fa-calendar" style="color:var(--accent)"></i> Move-in: <strong><?= date('d M Y',strtotime($b['move_in_date'])) ?></strong> · <?= $b['duration_months'] ?> month<?=$b['duration_months']>1?'s':''?></div>
            </div>
            <div style="text-align:right;flex-shrink:0">
              <div style="font-size:22px;font-weight:800;color:var(--primary)"><?= format_currency($b['total_amount']) ?></div>
              <?php if ($b['advance_paid']>0): ?><div style="font-size:12px;color:var(--success)">Paid online: <?= format_currency($b['advance_paid']) ?></div><?php endif; ?>
              <div style="margin-top:8px"><?= status_badge($b['status']) ?></div>
              <?php if ($b['rejection_reason']): ?><div style="font-size:11px;color:var(--danger);margin-top:4px"><?= htmlspecialchars(truncate($b['rejection_reason'],40)) ?></div><?php endif; ?>
            </div>
          </div>
          <?php if ($b['status']==='pending'): ?>
          <div class="card-footer" style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap">
            <a href="chat.php?with=<?= $b['student_id'] ?>&pg_id=<?= $b['pg_id'] ?>" class="btn btn-ghost btn-sm"><i class="fas fa-comment"></i> Chat</a>
            <form method="POST" style="display:flex;gap:8px">
              <?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
              <button name="action" value="confirm" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Accept Booking</button>
            </form>
            <button class="btn btn-danger btn-sm" onclick="document.getElementById('reject-<?= $b['id'] ?>').style.display='block'"><i class="fas fa-times"></i> Reject</button>
          </div>
          <div id="reject-<?= $b['id'] ?>" style="display:none;padding:0 24px 16px">
            <form method="POST">
              <?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><input type="hidden" name="action" value="reject">
              <div class="form-group"><label class="form-label">Reason for rejection</label><textarea name="reason" class="form-control" rows="2" placeholder="Explain why you're rejecting this booking..." required></textarea></div>
              <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
              <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('reject-<?= $b['id'] ?>').style.display='none'">Cancel</button>
            </form>
          </div>
          <?php else: ?>
          <div class="card-footer" style="display:flex;gap:10px;justify-content:flex-end">
            <a href="chat.php?with=<?= $b['student_id'] ?>&pg_id=<?= $b['pg_id'] ?>" class="btn btn-ghost btn-sm"><i class="fas fa-comment"></i> Chat</a>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?= pagination_html($pag,'?filter='.$filter) ?>
    <?php endif; ?>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
<script>var BASE_URL='<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
