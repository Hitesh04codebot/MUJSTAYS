<?php
// admin/users.php — User Management
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('admin');

if ($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf()) {
    $uid2  = (int)($_POST['user_id']??0);
    $action= $_POST['action']??'';
    if ($uid2 && $uid2 !== current_user_id()) {
        if ($action==='block')       $pdo->prepare("UPDATE users SET is_active=0 WHERE id=?")->execute([$uid2]);
        elseif ($action==='unblock') $pdo->prepare("UPDATE users SET is_active=1 WHERE id=?")->execute([$uid2]);
        elseif ($action==='verify_kyc') {
            $pdo->prepare("UPDATE users SET is_kyc_verified=1 WHERE id=?")->execute([$uid2]);
            $pdo->prepare("UPDATE kyc_documents SET status='approved',reviewed_at=NOW() WHERE owner_id=? AND status='pending'")->execute([$uid2]);
            create_notification($pdo,$uid2,'kyc_approved','KYC Verified! ✅','Your identity has been verified. Your listings are now eligible for approval.','/owner/profile.php');
            flash_set('success','KYC verified for user.');
        } elseif ($action==='reject_kyc') {
            $pdo->prepare("UPDATE users SET is_kyc_verified=0 WHERE id=?")->execute([$uid2]);
            $pdo->prepare("UPDATE kyc_documents SET status='rejected',reviewed_at=NOW() WHERE owner_id=? AND status='pending'")->execute([$uid2]);
            create_notification($pdo,$uid2,'kyc_rejected','KYC Not Approved','Your KYC verification was not approved. Please re-upload valid documents.','/owner/profile.php');
            flash_set('success','KYC rejected.');
        }
    }
    header('Location: users.php'); exit;
}

$role_filter = $_GET['role']??'all';
$q = trim($_GET['q']??'');
$filter = $_GET['filter']??'';
$where='1=1'; $params=[];
if (in_array($role_filter,['student','owner','admin'])) { $where.=" AND u.role=?"; $params[]=$role_filter; }
if ($q) { $where.=" AND (u.name LIKE ? OR u.email LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; }
if ($filter==='kyc_pending') { $where.=" AND u.role='owner' AND EXISTS (SELECT 1 FROM kyc_documents WHERE owner_id=u.id AND status='pending')"; }
if ($filter==='blocked') { $where.=" AND u.is_active=0"; }

$total=$pdo->prepare("SELECT COUNT(*) FROM users u WHERE $where"); $total->execute($params); $total=(int)$total->fetchColumn();
$pag=paginate($total,20);
$users=$pdo->prepare("SELECT u.*, 
    (SELECT COUNT(*) FROM pg_listings WHERE owner_id=u.id AND status='approved') AS active_listings, 
    (SELECT COUNT(*) FROM bookings WHERE student_id=u.id) AS total_bookings,
    k.file_path AS kyc_path, k.doc_type AS kyc_type
    FROM users u 
    LEFT JOIN kyc_documents k ON k.owner_id=u.id AND k.status='pending'
    WHERE $where 
    ORDER BY u.created_at DESC LIMIT {$pag['per_page']} OFFSET {$pag['offset']}");
$users->execute($params); $users=$users->fetchAll();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>User Management — MUJSTAYS Admin</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
  <?php require_once '../components/sidebar.php'; ?>
  <div class="main-content">
    <?php if ($m=flash_get('success')): ?><div class="alert alert-success" data-dismiss="4000"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($m) ?></div><?php endif; ?>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
      <h2 style="margin:0">User Management <span style="font-size:16px;color:var(--text-muted);font-weight:400">(<?= $total ?>)</span></h2>
      <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap">
        <input type="hidden" name="role" value="<?= htmlspecialchars($role_filter) ?>">
        <div class="input-group"><i class="fas fa-search input-icon"></i>
        <input type="text" name="q" class="form-control" placeholder="Search name or email…" value="<?= htmlspecialchars($q) ?>"></div>
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
      </form>
    </div>
    <!-- Role tabs -->
    <div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap">
      <?php foreach(['all'=>'👥 All','student'=>'🎓 Students','owner'=>'🏘️ Owners','admin'=>'⚙️ Admins'] as $k=>$v): ?>
        <a href="?role=<?= $k ?>&q=<?= urlencode($q) ?>" class="btn <?= $role_filter===$k?'btn-primary':'btn-secondary' ?> btn-sm"><?= $v ?></a>
      <?php endforeach; ?>
      <a href="?filter=kyc_pending" class="btn <?= $filter==='kyc_pending'?'btn-warning':'btn-secondary' ?> btn-sm">⏳ KYC Pending</a>
      <a href="?filter=blocked"     class="btn <?= $filter==='blocked'?'btn-danger':'btn-secondary' ?> btn-sm">🚫 Blocked</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>User</th><th>Role</th><th>Status</th><th>KYC</th><th>Activity</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach($users as $u): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:10px">
                <div style="width:36px;height:36px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0"><?= strtoupper(mb_substr($u['name'],0,1)) ?></div>
                <div>
                  <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($u['name']) ?></div>
                  <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($u['email']) ?></div>
                  <?php if ($u['phone']): ?><div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($u['phone']) ?></div><?php endif; ?>
                </div>
              </div>
            </td>
            <td><span class="badge <?= ['student'=>'badge-info','owner'=>'badge-blue','admin'=>'badge-primary'][$u['role']]??'badge-secondary' ?>"><?= ucfirst($u['role']) ?></span></td>
            <td>
              <?php if (!$u['is_active']): ?><span class="badge badge-danger">Blocked</span>
              <?php elseif ($u['is_verified']): ?><span class="badge badge-success">Active</span>
              <?php else: ?><span class="badge badge-warning">Unverified</span><?php endif; ?>
            </td>
            <td>
              <?php if ($u['role']==='owner'): ?>
                <?php if ($u['is_kyc_verified']): ?><span class="badge badge-success">✓ Verified</span>
                <?php else: ?>
                  <span class="badge badge-warning">Pending</span>
                  <?php if (!empty($u['kyc_path'])): ?>
                    <div style="margin-top:4px"><a href="<?= BASE_URL . '/' . $u['kyc_path'] ?>" target="_blank" class="btn btn-ghost btn-sm" style="padding:2px 5px;font-size:10px;color:var(--accent)"><i class="fas fa-file-alt"></i> View ID Proof</a></div>
                  <?php endif; ?>
                  <form method="POST" style="display:inline-block;margin-top:4px">
                    <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button name="action" value="verify_kyc" class="btn btn-success btn-sm" style="padding:3px 8px;font-size:11px" onclick="return confirm('Verify KYC for this owner?')">Verify</button>
                    <button name="action" value="reject_kyc" class="btn btn-danger btn-sm" style="padding:3px 8px;font-size:11px">Reject</button>
                  </form>
                <?php endif; ?>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td style="font-size:12px;color:var(--text-muted)">
              <?php if ($u['role']==='student'): ?><?= $u['total_bookings'] ?> booking<?=$u['total_bookings']!=1?'s':''?>
              <?php elseif ($u['role']==='owner'): ?><?= $u['active_listings'] ?> active listing<?=$u['active_listings']!=1?'s':''?><?php endif; ?>
            </td>
            <td style="font-size:12px;color:var(--text-muted)"><?= date('d M Y',strtotime($u['created_at'])) ?></td>
            <td>
              <?php if ($u['id'] !== current_user_id()): ?>
              <form method="POST" style="display:inline">
                <?= csrf_field() ?><input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <?php if ($u['is_active']): ?>
                  <button name="action" value="block" class="btn btn-danger btn-sm" onclick="return confirm('Block this user?')" title="Block user">🚫</button>
                <?php else: ?>
                  <button name="action" value="unblock" class="btn btn-success btn-sm" onclick="return confirm('Unblock this user?')" title="Unblock">✓</button>
                <?php endif; ?>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?= pagination_html($pag,'?role='.$role_filter.'&q='.urlencode($q)) ?>
  </div>
</div>
<?php require_once '../components/footer.php'; ?>
<script>var BASE_URL='<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
