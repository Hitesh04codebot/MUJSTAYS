<?php
// explore.php — Explore / All Listings Page
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_once 'includes/helpers.php';

// ---- Build dynamic WHERE clause from GET params ----
$where   = ["p.status = 'approved'", "p.is_deleted = 0"];
$params  = [];

$q         = trim($_GET['q'] ?? '');
$gender    = trim($_GET['gender'] ?? '');
$min_price = (int)($_GET['min_price'] ?? 0);
$max_price = (int)($_GET['max_price'] ?? 0);
$distance  = (float)($_GET['distance'] ?? 0);
$sort      = $_GET['sort'] ?? 'relevance';
$room_types= (array)($_GET['room_type'] ?? []);
$amenities = (array)($_GET['amenities'] ?? []);

if ($q) {
    $where[] = "(p.title LIKE ? OR a.name LIKE ? OR p.address LIKE ?)";
    $params  = array_merge($params, ["%$q%", "%$q%", "%$q%"]);
}

if ($gender) {
    $where[] = "p.gender_preference = ?";
    $params[] = $gender;
}
if ($min_price > 0) {
    $where[] = "p.price_min >= ?";
    $params[] = $min_price;
}
if ($max_price > 0) {
    $where[] = "p.price_max <= ?";
    $params[] = $max_price;
}
if ($distance > 0) {
    $where[] = "p.distance_from_muj <= ?";
    $params[] = $distance;
}
if (!empty($room_types)) {
    $placeholders = implode(',', array_fill(0, count($room_types), '?'));
    $where[] = "EXISTS (SELECT 1 FROM room_types rt WHERE rt.pg_id = p.id AND rt.type IN ($placeholders))";
    $params = array_merge($params, $room_types);
}
$amenity_flags = ['has_wifi','has_ac','has_food','has_parking','has_laundry','has_gym','has_cctv','has_warden','has_transport'];
foreach ($amenities as $am) {
    if (in_array($am, $amenity_flags)) $where[] = "p.$am = 1";
}

$where_sql = implode(' AND ', $where);

// Count total results
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM pg_listings p JOIN areas a ON p.area_id = a.id WHERE $where_sql");
$count_stmt->execute($params);
$total = (int)$count_stmt->fetchColumn();

// Pagination
$pag = paginate($total, 12);

// Sort
$order_sql = match($sort) {
    'price_asc'  => 'p.price_min ASC',
    'price_desc' => 'p.price_max DESC',
    'rating'     => 'p.avg_rating DESC, p.total_reviews DESC',
    'newest'     => 'p.created_at DESC',
    'distance'   => 'p.distance_from_muj ASC',
    default      => 'p.is_featured DESC, p.avg_rating DESC',
};

// Fetch PGs
$stmt = $pdo->prepare("
    SELECT p.*, a.name AS area_name,
           (SELECT file_path FROM pg_images WHERE pg_id = p.id AND is_cover = 1 LIMIT 1) AS cover_image,
           u.name AS owner_name, u.is_kyc_verified
    FROM pg_listings p
    JOIN users u ON u.id = p.owner_id
    JOIN areas a ON a.id = p.area_id
    WHERE $where_sql
    ORDER BY $order_sql
    LIMIT {$pag['per_page']} OFFSET {$pag['offset']}
");
$stmt->execute($params);
$listings = $stmt->fetchAll();

// Pagination URL
$get_without_page = array_filter($_GET, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY);
$base_paginate_url = '?' . http_build_query($get_without_page);
?>
<!DOCTYPE html>
<html lang="en" data-base-url="<?= BASE_URL ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Explore PGs Near MUJ — MUJSTAYS</title>
  <meta name="description" content="Browse <?= $total ?> verified PGs near Manipal University Jaipur. Filter by price, amenities, gender preference and more.">
  <meta name="csrf-token" content="<?= csrf_token() ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<?php require_once 'components/navbar.php'; ?>

<!-- Page Header -->
<div class="page-header" style="background: url('<?= BASE_URL ?>/assets/img/explorepg.jpg') center/cover no-repeat; position: relative;">
  <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(26,60,94,0.9) 0%, rgba(46,134,171,0.7) 100%); z-index: 1;"></div>
  <div class="container" style="position: relative; z-index: 2;">
    <div class="breadcrumb">
      <a href="<?= BASE_URL ?>">Home</a>
      <span class="breadcrumb-sep">›</span>
      <span>Explore PGs</span>
    </div>
    <h1>Explore PGs Near MUJ</h1>

    <div style="display:flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <p style="margin:0">
          <?= number_format($total) ?> verified PG<?= $total !== 1 ? 's' : '' ?> found
          <?= $q ? "matching <strong>\"" . htmlspecialchars($q) . "\"</strong>" : '' ?>
        </p>
        <div class="view-toggle" style="display:flex; background: var(--bg2); padding: 5px; border-radius: 30px; border: 1px solid var(--border);">
            <button class="btn btn-sm active" style="border-radius: 25px; padding: 6px 20px;"><i class="fas fa-th-large"></i> Grid</button>
            <button class="btn btn-sm" style="border-radius: 25px; padding: 6px 20px; background:none; color: var(--text-muted);" title="Map View coming soon"><i class="fas fa-map-marked-alt"></i> Map</button>
        </div>
    </div>
  </div>
</div>

<div class="section-sm">
  <div class="container">

    <!-- Mobile filter toggle -->
    <div style="display:none" id="mobile-filter-toggle">
      <button onclick="document.getElementById('filter-sidebar').style.display = document.getElementById('filter-sidebar').style.display === 'block' ? 'none' : 'block'" class="btn btn-outline btn-w100 mb-16">
        <i class="fas fa-sliders-h"></i> Show / Hide Filters
      </button>
    </div>

    <div class="explore-layout">
      <!-- Filter Sidebar -->
      <?php require_once 'components/filter-sidebar.php'; ?>

      <!-- Results -->
      <div>
        <!-- Toolbar -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
          <div style="font-size:14px;color:var(--text-muted)">
            Showing <strong><?= count($listings) ?></strong> of <strong><?= $total ?></strong> PGs
            <?php if ($pag['total_pages'] > 1): ?>
              — Page <?= $pag['current'] ?> of <?= $pag['total_pages'] ?>
            <?php endif; ?>
          </div>
          <div style="display:flex;gap:8px;align-items:center">
            <select class="form-select" style="width:auto" onchange="window.location.href=this.value">
              <?php
              $sorts = ['relevance'=>'Relevance','price_asc'=>'Price: Low–High','price_desc'=>'Price: High–Low','rating'=>'Rating','newest'=>'Newest','distance'=>'Distance'];
              foreach ($sorts as $val => $label):
                $url = '?' . http_build_query(array_merge($_GET, ['sort'=>$val, 'page'=>1]));
              ?>
              <option value="<?= $url ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Active filter pills -->
        <?php if ($q || $gender || $min_price || $max_price || $distance || !empty($room_types) || !empty($amenities)): ?>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
          <?php if ($q): ?><span class="badge badge-info" style="cursor:pointer" onclick="window.location.href='<?= BASE_URL ?>/explore.php?' + '<?= http_build_query(array_filter($_GET, fn($k)=>$k!=='q', ARRAY_FILTER_USE_KEY)) ?>'">🔍 "<?= htmlspecialchars($q) ?>" ✕</span><?php endif; ?>

          <?php if ($gender): ?><span class="badge badge-info"><?= $gender === 'male' ? '♂ Boys' : '♀ Girls' ?></span><?php endif; ?>
          <?php if ($min_price || $max_price): ?><span class="badge badge-info">₹<?= number_format($min_price) ?> – ₹<?= number_format($max_price) ?></span><?php endif; ?>
          <a href="<?= BASE_URL ?>/explore.php" class="badge badge-danger" style="cursor:pointer">⟳ Clear All</a>
        </div>
        <?php endif; ?>

        <!-- Results Grid -->
        <?php if (empty($listings)): ?>
          <div class="empty-state">
            <i class="fas fa-home"></i>
            <h3>No PGs Found</h3>
            <p>We couldn't find PGs matching your filters. Try adjusting your search criteria.</p>
            <a href="<?= BASE_URL ?>/explore.php" class="btn btn-primary">Clear All Filters</a>
          </div>
        <?php else: ?>
          <div class="pg-grid">
            <?php foreach ($listings as $pg): ?>
              <?php require 'components/pg-card.php'; ?>
            <?php endforeach; ?>
          </div>
          <?= pagination_html($pag, $base_paginate_url) ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>


<?php require_once 'components/footer.php'; ?>
<style>
@media(max-width:991px){#mobile-filter-toggle{display:block!important}#filter-sidebar{display:none}}
</style>
</body>
</html>

