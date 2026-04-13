<?php
// user/bookings.php — Student Booking History
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('student');
$uid = current_user_id();

// Handle cancel
if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['cancel_booking'])) {
    if (!verify_csrf()) { flash_set('error','Invalid request.'); }
    else {
        $bid = (int)$_POST['booking_id'];
        $check = $pdo->prepare("SELECT * FROM bookings WHERE id=? AND student_id=? AND status='pending'");
        $check->execute([$bid,$uid]); $bk=$check->fetch();
        if ($bk) {
            $pdo->prepare("UPDATE bookings SET status='cancelled',updated_at=NOW() WHERE id=?")->execute([$bid]);
            // Restore bed
            $pdo->prepare("UPDATE room_types SET available_beds=available_beds+1 WHERE id=?")->execute([$bk['room_type_id']]);
            flash_set('success','Booking cancelled successfully.');
        }
    }
    header('Location: bookings.php'); exit;
}

$filter = $_GET['filter'] ?? 'all';
$where = "b.student_id=?";
$params = [$uid];
if (in_array($filter,['pending','confirmed','rejected','cancelled','completed'])) {
    $where .= " AND b.status=?"; $params[]=$filter;
}

$total = $pdo->prepare("SELECT COUNT(*) FROM bookings b WHERE $where"); $total->execute($params); $total=(int)$total->fetchColumn();
$pag = paginate($total, 10);
$bk = $pdo->prepare("SELECT b.*, p.title AS pg_title, p.area_name, p.id AS pg_id, rt.type AS room_type, rt.price_per_month, u.name AS owner_name FROM bookings b JOIN pg_listings p ON p.id=b.pg_id JOIN room_types rt ON rt.id=b.room_type_id JOIN users u ON u.id=b.owner_id WHERE $where ORDER BY b.created_at DESC LIMIT {$pag['per_page']} OFFSET {$pag['offset']}");
$bk->execute($params); $bookings=$bk->fetchAll();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Bookings — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
  <?php require_once '../components/sidebar.php'; ?>
  <div class="main-content">
    <?php if ($m=flash_get('success')): ?><div class="alert alert-success" data-dismiss="4000"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($m) ?></div><?php endif; ?>
    <?php if ($m=flash_get('error')): ?><div class="alert alert-error"><?= htmlspecialchars($m) ?></div><?php endif; ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
      <h2 style="margin:0">My Bookings</h2>
      <a href="<?= BASE_URL ?>/explore.php" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Find New PG</a>
    </div>

    <!-- Filter tabs -->
    <div class="tabs" style="margin-bottom:24px">
      <?php foreach(['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','cancelled'=>'Cancelled','completed'=>'Completed'] as $k=>$v): ?>
        <button class="tab-btn <?= $filter===$k?'active':'' ?>" onclick="window.location.href='?filter=<?= $k ?>'">
          <?= $v ?>
        </button>
      <?php endforeach; ?>
    </div>

    <?php if (empty($bookings)): ?>
      <div class="empty-state"><i class="fas fa-calendar-times"></i><h3>No Bookings Found</h3><p>You haven't made any bookings yet.</p><a href="<?= BASE_URL ?>/explore.php" class="btn btn-primary">Explore PGs</a></div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>PG Name</th><th>Room</th><th>Move-in Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach($bookings as $b): ?>
            <tr>
              <td>
                <a href="<?= BASE_URL ?>/pg-detail.php?id=<?= $b['pg_id'] ?>" style="font-weight:600;color:var(--primary)"><?= htmlspecialchars(truncate($b['pg_title'],35)) ?></a>
                <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($b['area_name']) ?> · Owner: <?= htmlspecialchars($b['owner_name']) ?></div>
              </td>
              <td><?= ucfirst($b['room_type']) ?></td>
              <td><?= date('d M Y',strtotime($b['move_in_date'])) ?></td>
              <td style="font-weight:700"><?= format_currency($b['total_amount']) ?><?php if ($b['advance_paid']>0): ?><div style="font-size:11px;color:var(--success)">Paid: <?= format_currency($b['advance_paid']) ?></div><?php endif; ?></td>
              <td><?= status_badge($b['status']) ?><?php if ($b['rejection_reason']): ?><div style="font-size:11px;color:var(--danger);margin-top:4px" title="<?= htmlspecialchars($b['rejection_reason']) ?>">Reason: <?= truncate($b['rejection_reason'],30) ?></div><?php endif; ?></td>
              <td>
                <div style="display:flex;gap:6px;flex-wrap:wrap">
                  <a href="<?= BASE_URL ?>/pg-detail.php?id=<?= $b['pg_id'] ?>" class="btn btn-ghost btn-sm"><i class="fas fa-eye"></i></a>
                  <a href="chat.php?with=<?= $b['owner_id'] ?>&pg_id=<?= $b['pg_id'] ?>" class="btn btn-ghost btn-sm" title="Chat"><i class="fas fa-comment"></i></a>
                  <?php if ($b['status']==='pending'): ?>
                  <form method="POST" onsubmit="return confirm('Cancel this booking?')">
                    <?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                    <button type="submit" name="cancel_booking" class="btn btn-danger btn-sm">Cancel</button>
                  </form>
                  <?php endif; ?>
                  <?php if ($b['status']==='completed'): ?>
                  <a href="reviews.php?booking_id=<?= $b['id'] ?>&pg_id=<?= $b['pg_id'] ?>" class="btn btn-accent btn-sm"><i class="fas fa-star"></i> Review</a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?= pagination_html($pag, '?filter='.$filter) ?>
    <?php endif; ?>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
</body></html>
