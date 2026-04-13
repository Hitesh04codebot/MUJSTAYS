<?php
// admin/listings.php — Admin Listing Moderation
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('admin');

// Handle approve/reject/feature
if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $lid    = (int)($_POST['listing_id']??0);
    $action = $_POST['action']??'';
    $reason = sanitize($_POST['reason']??'');
    $listing = $pdo->prepare("SELECT * FROM pg_listings WHERE id=?"); $listing->execute([$lid]); $listing=$listing->fetch();
    if ($listing) {
        if ($action==='approve') {
            $pdo->prepare("UPDATE pg_listings SET status='approved',rejection_reason=NULL WHERE id=?")->execute([$lid]);
            create_notification($pdo,$listing['owner_id'],'listing_approved','Listing Approved! 🎉',"Your listing '{$listing['title']}' has been approved and is now live!",'/owner/listings.php');
            flash_set('success','Listing approved and is now live.');
        } elseif ($action==='reject') {
            $pdo->prepare("UPDATE pg_listings SET status='rejected',rejection_reason=? WHERE id=?")->execute([$reason,$lid]);
            create_notification($pdo,$listing['owner_id'],'listing_rejected','Listing Not Approved',"Your listing '{$listing['title']}' was not approved. Reason: $reason",'/owner/listings.php');
            flash_set('success','Listing rejected.');
        } elseif ($action==='feature') {
            $new = $listing['is_featured'] ? 0 : 1;
            $pdo->prepare("UPDATE pg_listings SET is_featured=? WHERE id=?")->execute([$new,$lid]);
            flash_set('success','Featured status updated.');
        } elseif ($action==='delete') {
            $pdo->prepare("UPDATE pg_listings SET is_deleted=1 WHERE id=?")->execute([$lid]);
            flash_set('success','Listing removed.');
        }
    }
    header('Location: listings.php?status='.($_GET['status']??'')); exit;
}

$status_filter = $_GET['status']??'pending';
$q = trim($_GET['q']??'');
$where = "p.is_deleted=0"; $params=[];
if (in_array($status_filter,['pending','approved','rejected','draft','inactive'])) { $where.=" AND p.status=?"; $params[]=$status_filter; }
if ($q) { $where.=" AND (p.title LIKE ? OR u.email LIKE ? OR u.name LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; $params[]="%$q%"; }
$total=$pdo->prepare("SELECT COUNT(*) FROM pg_listings p JOIN users u ON u.id=p.owner_id WHERE $where"); $total->execute($params); $total=(int)$total->fetchColumn();
$pag=paginate($total,15);
$listings=$pdo->prepare("SELECT p.*, a.name AS area_name, u.name AS owner_name, u.email AS owner_email, u.is_kyc_verified, (SELECT COUNT(*) FROM pg_images WHERE pg_id=p.id) AS image_count, (SELECT COUNT(*) FROM room_types WHERE pg_id=p.id) AS room_count FROM pg_listings p JOIN users u ON u.id=p.owner_id LEFT JOIN areas a ON a.id = p.area_id WHERE $where ORDER BY p.created_at DESC LIMIT {$pag['per_page']} OFFSET {$pag['offset']}");
$listings->execute($params); $listings=$listings->fetchAll();

// Status counts
$counts=$pdo->query("SELECT status,COUNT(*) cnt FROM pg_listings WHERE is_deleted=0 GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Listing Moderation — MUJSTAYS Admin</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
  <?php require_once '../components/sidebar.php'; ?>
  <div class="main-content">
    <?php if ($m=flash_get('success')): ?><div class="alert alert-success" data-dismiss="4000"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($m) ?></div><?php endif; ?>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
      <h2 style="margin:0">Listing Moderation</h2>
      <form style="display:flex;gap:8px" method="GET">
        <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
        <div class="input-group"><i class="fas fa-search input-icon"></i>
        <input type="text" name="q" class="form-control" placeholder="Search by name or owner..." value="<?= htmlspecialchars($q) ?>"></div>
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
      </form>
    </div>
    <!-- Status Tabs -->
    <div style="display:flex;gap:4px;margin-bottom:24px;flex-wrap:wrap">
      <?php foreach(['pending'=>'⏳ Pending','approved'=>'✅ Approved','rejected'=>'❌ Rejected','all'=>'📋 All'] as $k=>$v):
        $cnt = $k==='all' ? array_sum($counts) : ($counts[$k]??0); ?>
        <a href="?status=<?= $k ?>" class="btn <?= $status_filter===$k?'btn-primary':'btn-secondary' ?> btn-sm">
          <?= $v ?> <?php if($cnt): ?><span class="badge <?= $status_filter===$k?'badge-primary':'badge-secondary' ?>" style="margin-left:4px"><?= $cnt ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if (empty($listings)): ?>
      <div class="empty-state"><i class="fas fa-home"></i><h3>No Listings Found</h3><p>No listings match your current filter.</p></div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th><input type="checkbox" id="select-all"></th><th>PG Name</th><th>Owner</th><th>Area</th><th>Quality</th><th>Status</th><th>Submitted</th><th>Actions</th></tr></thead>
          <tbody>
            <?php foreach($listings as $l): ?>
            <tr>
              <td><input type="checkbox" class="row-check" value="<?= $l['id'] ?>"></td>
              <td>
                <a href="<?= BASE_URL ?>/pg-detail.php?id=<?= $l['id'] ?>" target="_blank" style="font-weight:700;color:var(--primary)"><?= htmlspecialchars(truncate($l['title'],35)) ?></a>
                <?php if ($l['is_featured']): ?><span class="badge badge-primary" style="font-size:10px;margin-left:4px">⭐</span><?php endif; ?>
              </td>
              <td>
                <div style="font-size:13px"><?= htmlspecialchars($l['owner_name']) ?></div>
                <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($l['owner_email']) ?></div>
                <?php if ($l['is_kyc_verified']): ?><span class="badge badge-success" style="font-size:10px"><i class="fas fa-shield-alt"></i> KYC</span><?php endif; ?>
              </td>
              <td><?= htmlspecialchars($l['area_name']) ?></td>
              <td>
                <div style="font-size:12px">
                  <?php
                  $checks = [
                    $l['image_count']>=3 => '📸 '.$l['image_count'].' photos',
                    strlen($l['description'])>=100 => '📝 Good desc',
                    $l['room_count']>0 => '🛏 '.$l['room_count'].' room type'.(($l['room_count']>1)?'s':''),
                    !empty($l['latitude']) => '📍 Coords set',
                  ];
                  foreach($checks as $pass=>$label): ?>
                    <div style="color:<?= $pass?'var(--success)':'var(--danger)' ?>"><?= $pass?'✓':'✗' ?> <?= $label ?></div>
                  <?php endforeach; ?>
                </div>
              </td>
              <td><?= status_badge($l['status']) ?></td>
              <td style="font-size:12px;color:var(--text-muted)"><?= date('d M Y',strtotime($l['created_at'])) ?></td>
              <td>
                <div style="display:flex;gap:4px;flex-wrap:wrap">
                  <?php if ($l['status']==='pending'): ?>
                  <form method="POST" style="display:inline">
                    <?= csrf_field() ?><input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                    <button name="action" value="approve" class="btn btn-success btn-sm" onclick="return confirm('Approve this listing?')">✓ Approve</button>
                  </form>
                  <button class="btn btn-danger btn-sm" onclick="document.getElementById('rej-<?= $l['id'] ?>').style.display='block'">✗ Reject</button>
                  <?php elseif ($l['status']==='approved'): ?>
                  <form method="POST" style="display:inline">
                    <?= csrf_field() ?><input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                    <button name="action" value="feature" class="btn btn-accent btn-sm"><?= $l['is_featured']?'★ Unfeature':'☆ Feature' ?></button>
                  </form>
                  <?php endif; ?>
                  <form method="POST" style="display:inline">
                    <?= csrf_field() ?><input type="hidden" name="listing_id" value="<?= $l['id'] ?>">
                    <button name="action" value="delete" class="btn btn-ghost btn-sm" onclick="return confirm('Remove this listing?')" title="Remove"><i class="fas fa-trash"></i></button>
                  </form>
                </div>
                <!-- Reject inline -->
                <div id="rej-<?= $l['id'] ?>" style="display:none;margin-top:8px;min-width:220px">
                  <form method="POST">
                    <?= csrf_field() ?><input type="hidden" name="listing_id" value="<?= $l['id'] ?>"><input type="hidden" name="action" value="reject">
                    <textarea name="reason" class="form-control" rows="2" style="font-size:12px" placeholder="Rejection reason…" required></textarea>
                    <div style="display:flex;gap:4px;margin-top:4px">
                      <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                      <button type="button" class="btn btn-ghost btn-sm" onclick="document.getElementById('rej-<?= $l['id'] ?>').style.display='none'">Cancel</button>
                    </div>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?= pagination_html($pag,'?status='.$status_filter.'&q='.urlencode($q)) ?>
    <?php endif; ?>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
</body></html>
