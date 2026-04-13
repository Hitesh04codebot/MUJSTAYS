<?php
// admin/bookings.php — Admin All Bookings View
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';

require_auth('admin');

$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$where = "1=1";
$params = [];

if (in_array($filter, ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'])) {
    $where .= " AND b.status = ?";
    $params[] = $filter;
}

if ($search) {
    $where .= " AND (u.name LIKE ? OR p.title LIKE ? OR ow.name LIKE ?)";
    $likeSearch = "%$search%";
    $params[] = $likeSearch;
    $params[] = $likeSearch;
    $params[] = $likeSearch;
}

// Pagination logic
$total_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings b JOIN users u ON u.id = b.student_id JOIN pg_listings p ON p.id = b.pg_id JOIN users ow ON ow.id = b.owner_id WHERE $where");
$total_stmt->execute($params);
$total = (int)$total_stmt->fetchColumn();

$pag = paginate($total, 15);

// Fetch bookings
$stmt = $pdo->prepare("
    SELECT b.*, 
           p.title AS pg_title, p.slug AS pg_slug,
           u.name AS student_name, u.email AS student_email,
           ow.name AS owner_name, ow.email AS owner_email,
           rt.type AS room_type
    FROM bookings b
    JOIN pg_listings p ON p.id = b.pg_id
    JOIN users u ON u.id = b.student_id
    JOIN users ow ON ow.id = b.owner_id
    JOIN room_types rt ON rt.id = b.room_type_id
    WHERE $where
    ORDER BY b.created_at DESC
    LIMIT {$pag['per_page']} OFFSET {$pag['offset']}
");
$stmt->execute($params);
$bookings = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>All Bookings — Admin Panel</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
    <?php require_once '../components/sidebar.php'; ?>

    <div class="main-content">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
            <h2 style="margin:0">All Platform Bookings</h2>
            <form action="" method="GET" style="display:flex;gap:8px">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search student, PG or owner..." value="<?= htmlspecialchars($search) ?>">
                <?php if($filter !== 'all'): ?><input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>"><?php endif; ?>
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </form>
        </div>

        <div class="tabs" style="margin-bottom:24px">
            <?php foreach(['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','rejected'=>'Rejected','cancelled'=>'Cancelled','completed'=>'Completed'] as $k=>$v): ?>
                <a href="?filter=<?= $k ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="tab-btn <?= $filter===$k?'active':'' ?>" style="text-decoration:none">
                    <?= $v ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID & Date</th>
                            <th>Student</th>
                            <th>Property / Room</th>
                            <th>Owner</th>
                            <th>Move-in</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($bookings)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
                                    No bookings found matching the filters.
                                </td>
                            </tr>
                        <?php else: foreach($bookings as $b): ?>
                            <tr>
                                <td>
                                    <div class="fw-700">#BK-<?= $b['id'] ?></div>
                                    <div class="small text-muted"><?= date('d M Y', strtotime($b['created_at'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-600"><?= htmlspecialchars($b['student_name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($b['student_email']) ?></div>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/pg-detail.php?slug=<?= $b['pg_slug'] ?>" style="font-weight:600;color:var(--primary);text-decoration:none" target="_blank">
                                        <?= htmlspecialchars(truncate($b['pg_title'], 25)) ?>
                                    </a>
                                    <div class="small text-muted"><?= ucfirst($b['room_type']) ?> Room</div>
                                </td>
                                <td>
                                    <div class="fw-600"><?= htmlspecialchars($b['owner_name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($b['owner_email']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-600"><?= date('d M Y', strtotime($b['move_in_date'])) ?></div>
                                    <div class="small text-muted"><?= $b['duration_months'] ?> Months</div>
                                </td>
                                <td>
                                    <div class="fw-700"><?= format_currency($b['total_amount']) ?></div>
                                    <?php if($b['advance_paid'] > 0): ?>
                                        <div class="small text-success">Adv: <?= format_currency($b['advance_paid']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= status_badge($b['status']) ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?= pagination_html($pag, "?filter=$filter" . ($search ? "&search=".urlencode($search) : "")) ?>
    </div>
</div>
<?php require_once '../components/footer.php'; ?>
</body>
</html>
