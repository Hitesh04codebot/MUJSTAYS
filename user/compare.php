<?php
// user/compare.php — Side-by-Side PG Comparison
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

$ids_str = $_GET['ids'] ?? '';
$ids = array_filter(array_map('intval', explode(',', $ids_str)));

if (empty($ids)) {
    redirect(BASE_URL . '/explore.php');
}

// Fetch PGs and their room types
$ids_placeholder = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("
    SELECT p.*, a.name as area_name 
    FROM pg_listings p 
    JOIN areas a ON p.area_id = a.id 
    WHERE p.id IN ($ids_placeholder) AND p.status = 'approved'
");
$stmt->execute($ids);
$pgs = $stmt->fetchAll();

if (empty($pgs)) {
    redirect(BASE_URL . '/explore.php');
}

// Fetch room types for these PGs
$room_data = [];
foreach ($ids as $id) {
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE pg_id = ? ORDER BY price_per_month ASC");
    $stmt->execute([$id]);
    $room_data[$id] = $stmt->fetchAll();
}

$amenities = [
    'has_wifi'    => ['label' => 'WiFi', 'icon' => 'fa-wifi'],
    'has_ac'      => ['label' => 'AC', 'icon' => 'fa-snowflake'],
    'has_food'    => ['label' => 'Food/Mess', 'icon' => 'fa-utensils'],
    'has_parking' => ['label' => 'Parking', 'icon' => 'fa-parking'],
    'has_laundry' => ['label' => 'Laundry', 'icon' => 'fa-tshirt'],
    'has_gym'     => ['label' => 'Gym', 'icon' => 'fa-dumbbell'],
    'has_cctv'    => ['label' => 'Security/CCTV', 'icon' => 'fa-video'],
    'has_warden'  => ['label' => 'Warden', 'icon' => 'fa-user-shield'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Compare PGs — MUJSTAYS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .compare-table-wrapper { overflow-x: auto; margin-top: 32px; border-radius: var(--radius-lg); border: 1px solid var(--border); background: #fff; }
        .compare-table { width: 100%; border-collapse: collapse; table-layout: fixed; min-width: 800px; }
        .compare-table th, .compare-table td { padding: 24px; border: 1px solid var(--border); text-align: center; vertical-align: top; }
        .compare-table .row-label { background: var(--bg2); width: 220px; font-weight: 700; color: var(--primary); text-align: left; position: sticky; left: 0; z-index: 10; }
        .compare-pg-header img { width: 100%; height: 160px; object-fit: cover; border-radius: var(--radius); margin-bottom: 16px; }
        .compare-pg-title { font-size: 16px; font-weight: 700; color: var(--primary); margin-bottom: 8px; }
        .compare-price { font-size: 20px; font-weight: 800; color: var(--accent); }
        .check-yes { color: var(--success); font-size: 18px; }
        .check-no { color: var(--text-light); font-size: 18px; opacity: 0.3; }
        .room-tag { display: inline-block; padding: 4px 10px; background: var(--bg2); border-radius: 6px; font-size: 12px; margin: 2px; }
    </style>
</head>
<body>
<?php require_once '../components/navbar.php'; ?>

<main class="section">
    <div class="container">
        <div class="section-title">
            <span class="accent-line"></span>
            <h2>PG Comparison</h2>
            <p>Compare prices, amenities, and room types to find your best fit.</p>
        </div>

        <div class="compare-table-wrapper">
            <table class="compare-table">
                <thead>
                    <tr>
                        <th class="row-label">Property</th>
                        <?php foreach ($pgs as $pg): ?>
                        <th style="vertical-align: middle;">
                            <div class="compare-pg-header">
                                <div class="compare-pg-title" style="font-size: 18px; margin-bottom: 8px;"><?= htmlspecialchars($pg['title']) ?></div>
                                <div class="compare-pg-location" style="font-size:13px; color:var(--text-muted); margin-bottom:16px;">
                                    <i class="fas fa-map-marker-alt" style="color:var(--accent);"></i> <?= htmlspecialchars($pg['area_name']) ?>
                                </div>
                                <a href="<?= BASE_URL ?>/pg-detail.php?id=<?= $pg['id'] ?>" class="btn btn-outline btn-sm" style="width: 100%;">View Details</a>
                            </div>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="row-label">Price Range</td>
                        <?php foreach ($pgs as $pg): ?>
                        <td>
                            <div class="compare-price">₹<?= number_format($pg['price_min']) ?>+</div>
                            <small class="text-muted">per month</small>
                        </td>
                        <?php endforeach; ?>
                    </tr>

                    <tr>
                        <td class="row-label">Room Types</td>
                        <?php foreach ($pgs as $pg): ?>
                        <td>
                            <?php foreach ($room_data[$pg['id']] as $r): ?>
                                <span class="room-tag"><?= htmlspecialchars(ucfirst($r['type'])) ?> (₹<?= number_format($r['price_per_month']) ?>)</span>
                            <?php endforeach; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>

                    <?php foreach ($amenities as $key => $a): ?>
                    <tr>
                        <td class="row-label"><i class="fas <?= $a['icon'] ?> mr-8" style="width:20px; color:var(--accent)"></i> <?= $a['label'] ?></td>
                        <?php foreach ($pgs as $pg): ?>
                        <td>
                            <?php if (!empty($pg[$key])): ?>
                                <i class="fas fa-check-circle check-yes"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle check-no"></i>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>

                    <tr>
                        <td class="row-label">Gender Preference</td>
                        <?php foreach ($pgs as $pg): ?>
                        <td><?= gender_badge($pg['gender_preference']) ?></td>
                        <?php endforeach; ?>
                    </tr>

                    <tr>
                        <td class="row-label">Food Inclusion</td>
                        <?php foreach ($pgs as $pg): ?>
                        <td>
                            <?php if ($pg['has_food']): ?>
                                <span class="badge badge-success">Included</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Not Included</span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>

                    <tr>
                        <td class="row-label">Action</td>
                        <?php foreach ($pgs as $pg): ?>
                        <td>
                            <a href="<?= BASE_URL ?>/pg-detail.php?id=<?= $pg['id'] ?>#book" class="btn btn-primary btn-sm">Book This PG</a>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-24 text-center">
            <a href="<?= BASE_URL ?>/explore.php" class="btn btn-secondary">&larr; Back to Explore</a>
        </div>
    </div>
</main>

<?php require_once '../components/footer.php'; ?>
</body>
</html>
