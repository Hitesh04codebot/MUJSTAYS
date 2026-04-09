<?php
// owner/listings.php — Owner My Listings
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('owner');
$uid = current_user_id();

// Handle toggle active/inactive
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $lid = (int)($_POST['listing_id']??0);
    $act = $_POST['action']??'';
    $own_check=$pdo->prepare("SELECT id FROM pg_listings WHERE id=? AND owner_id=?");
    $own_check->execute([$lid,$uid]);
    if ($own_check->fetch()) {
        if ($act==='deactivate') $pdo->prepare("UPDATE pg_listings SET status='inactive' WHERE id=?")->execute([$lid]);
        elseif ($act==='activate' && true) $pdo->prepare("UPDATE pg_listings SET status='approved' WHERE id=?")->execute([$lid]);
        elseif ($act==='delete') $pdo->prepare("UPDATE pg_listings SET is_deleted=1 WHERE id=?")->execute([$lid]);
        flash_set('success','Listing updated.');
    }
    header('Location: listings.php'); exit;
}

$filter=$_GET['status']??'all';
$where="p.owner_id=? AND p.is_deleted=0"; $params=[$uid];
if (in_array($filter,['approved','pending','rejected','inactive','draft'])) { $where.=" AND p.status=?"; $params[]=$filter; }
$total=$pdo->prepare("SELECT COUNT(*) FROM pg_listings p WHERE $where"); $total->execute($params); $total=(int)$total->fetchColumn();
$pag=paginate($total,10);
$listings=$pdo->prepare("SELECT p.*, a.name AS area_name, (SELECT file_path FROM pg_images WHERE pg_id=p.id AND is_cover=1 LIMIT 1) AS cover_image, (SELECT SUM(total_beds) FROM room_types WHERE pg_id=p.id) AS total_beds, (SELECT SUM(available_beds) FROM room_types WHERE pg_id=p.id) AS avail_beds, (SELECT COUNT(*) FROM bookings WHERE pg_id=p.id AND status='confirmed') AS active_bookings FROM pg_listings p LEFT JOIN areas a ON a.id = p.area_id WHERE $where ORDER BY p.created_at DESC LIMIT {$pag['per_page']} OFFSET {$pag['offset']}");
$listings->execute($params); $listings=$listings->fetchAll();

$counts=$pdo->prepare("SELECT status,COUNT(*) cnt FROM pg_listings WHERE owner_id=? AND is_deleted=0 GROUP BY status");
$counts->execute([$uid]); $counts=array_column($counts->fetchAll(),'cnt','status');
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Listings — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
  <div class="sidebar">
    <div class="sidebar-logo"><h3>🏘️ Owner</h3><p><?= htmlspecialchars($_SESSION['name']) ?></p></div>
    <nav class="sidebar-menu">
      <a href="dashboard.php"   class="sidebar-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="listings.php"    class="sidebar-link active"><i class="fas fa-home"></i> My Listings</a>
      <a href="add-listing.php" class="sidebar-link"><i class="fas fa-plus-circle"></i> Add Listing</a>
      <a href="bookings.php"    class="sidebar-link"><i class="fas fa-calendar-check"></i> Bookings</a>
      <a href="payments.php"    class="sidebar-link"><i class="fas fa-money-bill-wave"></i> Payments</a>
      <a href="chat.php"        class="sidebar-link"><i class="fas fa-comments"></i> Messages</a>
      <a href="reviews.php"     class="sidebar-link"><i class="fas fa-star"></i> Reviews</a>
      <a href="profile.php"     class="sidebar-link"><i class="fas fa-user"></i> Profile & KYC</a>
      <a href="<?= BASE_URL ?>/logout.php" class="sidebar-link" style="color:rgba(255,100,100,.8)"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
  </div>
  <div class="main-content">
    <?php if ($m=flash_get('success')): ?><div class="alert alert-success" data-dismiss="4000"><?= htmlspecialchars($m) ?></div><?php endif; ?>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
      <h2 style="margin:0">My Listings</h2>
      <a href="add-listing.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New PG</a>
    </div>
    <!-- Filter -->
    <div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap">
      <?php foreach(['all'=>'All','approved'=>'✅ Live','pending'=>'⏳ Pending','rejected'=>'❌ Rejected','inactive'=>'💤 Inactive'] as $k=>$v):
        $cnt=$k==='all'?array_sum($counts):($counts[$k]??0); ?>
        <a href="?status=<?= $k ?>" class="btn <?= $filter===$k?'btn-primary':'btn-secondary' ?> btn-sm"><?= $v ?> <?php if($cnt): ?>(<?= $cnt ?>)<?php endif; ?></a>
      <?php endforeach; ?>
    </div>
    <?php if (empty($listings)): ?>
      <div class="empty-state"><i class="fas fa-home"></i><h3>No Listings Yet</h3><p>Click "Add New PG" to get started.</p><a href="add-listing.php" class="btn btn-primary">Add Listing</a></div>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:16px">
        <?php foreach($listings as $l): $occ=$l['total_beds']>0?round((($l['total_beds']-$l['avail_beds'])/$l['total_beds'])*100):0; ?>
        <div class="card">
          <div class="card-body" style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">
            <img src="<?= $l['cover_image']?BASE_URL.'/'.$l['cover_image']:BASE_URL.'/assets/images/pg-placeholder.jpg' ?>" alt="" style="width:140px;height:90px;border-radius:10px;object-fit:cover;flex-shrink:0">
            <div style="flex:1;min-width:200px">
              <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px">
                <h3 style="margin:0;font-size:16px"><?= htmlspecialchars($l['title']) ?></h3>
                <?= status_badge($l['status']) ?>
                <?php if ($l['is_featured']): ?><span class="badge badge-primary" style="font-size:11px">⭐ Featured</span><?php endif; ?>
              </div>
              <div style="font-size:13px;color:var(--text-muted);margin-bottom:8px"><i class="fas fa-map-marker-alt" style="color:var(--accent)"></i> <?= htmlspecialchars($l['area_name']) ?></div>
              <?php if ($l['rejection_reason']): ?><div class="alert alert-error" style="padding:8px 12px;font-size:12px;margin-bottom:8px"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($l['rejection_reason']) ?></div><?php endif; ?>
              <div style="display:flex;gap:16px;font-size:13px;flex-wrap:wrap">
                <span><strong><?= format_currency($l['price_min']) ?></strong>/mo</span>
                <span>🛏 <?= $l['total_beds'] ?> beds · <?= $l['avail_beds'] ?> free</span>
                <span>📊 <?= $occ ?>% occupancy</span>
                <span>📅 <?= $l['active_bookings'] ?> active bookings</span>
                <span>👁 <?= number_format($l['view_count']) ?> views</span>
                <span>⭐ <?= number_format((float)$l['avg_rating'],1) ?> (<?= $l['total_reviews'] ?> reviews)</span>
              </div>
            </div>
            <div style="flex-shrink:0;display:flex;flex-direction:column;gap:8px;align-items:flex-end">
              <a href="<?= BASE_URL ?>/pg-detail.php?id=<?= $l['id'] ?>" class="btn btn-ghost btn-sm" target="_blank"><i class="fas fa-eye"></i> View</a>
              <a href="edit-listing.php?id=<?= $l['id'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-edit"></i> Edit</a>
              <?php if ($l['status']==='approved'): ?>
              <form method="POST">
                <?= csrf_field() ?><input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                <button name="action" value="deactivate" class="btn btn-warning btn-sm">Deactivate</button>
              </form>
              <?php elseif ($l['status']==='inactive'): ?>
              <form method="POST">
                <?= csrf_field() ?><input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                <button name="action" value="activate" class="btn btn-success btn-sm">Re-activate</button>
              </form>
              <?php endif; ?>
              <form method="POST" onsubmit="return confirm('Delete this listing permanently?')">
                <?= csrf_field() ?><input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                <button name="action" value="delete" class="btn btn-ghost btn-sm" style="color:var(--danger)"><i class="fas fa-trash"></i></button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?= pagination_html($pag,'?status='.$filter) ?>
    <?php endif; ?>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
<script>var BASE_URL='<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
