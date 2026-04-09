<?php
// pg-detail.php — Single PG Detail Page
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_once 'includes/helpers.php';

$pg_id = (int)($_GET['id'] ?? 0);
if (!$pg_id) { header('Location: ' . BASE_URL . '/explore.php'); exit; }

// Fetch PG
$stmt = $pdo->prepare("
    SELECT p.*, a.name AS area_name, u.name AS owner_name, u.phone AS owner_phone,
           u.profile_photo AS owner_photo, u.is_kyc_verified,
           u.id AS owner_user_id
    FROM pg_listings p
    JOIN users u ON u.id = p.owner_id
    JOIN areas a ON a.id = p.area_id
    WHERE p.id = ? AND p.status = 'approved' AND p.is_deleted = 0
");
$stmt->execute([$pg_id]);
$pg = $stmt->fetch();
if (!$pg) { header('Location: ' . BASE_URL . '/explore.php?error=notfound'); exit; }

// Log view
$pdo->prepare("UPDATE pg_listings SET view_count = view_count + 1 WHERE id = ?")->execute([$pg_id]);

// Fetch images
$img_stmt = $pdo->prepare("SELECT * FROM pg_images WHERE pg_id = ? ORDER BY is_cover DESC, sort_order ASC");
$img_stmt->execute([$pg_id]);
$images = $img_stmt->fetchAll();

// Fetch room types
$rt_stmt = $pdo->prepare("SELECT * FROM room_types WHERE pg_id = ? ORDER BY price_per_month ASC");
$rt_stmt->execute([$pg_id]);
$room_types = $rt_stmt->fetchAll();

// Fetch reviews
$rev_stmt = $pdo->prepare("
    SELECT r.*, u.name AS student_name, u.profile_photo AS student_photo
    FROM reviews r
    JOIN users u ON u.id = r.student_id
    WHERE r.pg_id = ? AND r.is_approved = 1
    ORDER BY r.created_at DESC LIMIT 20
");
$rev_stmt->execute([$pg_id]);
$reviews = $rev_stmt->fetchAll();

// Rating distribution
$dist_stmt = $pdo->prepare("SELECT rating, COUNT(*) AS cnt FROM reviews WHERE pg_id = ? AND is_approved = 1 GROUP BY rating");
$dist_stmt->execute([$pg_id]);
$rating_dist = [];
foreach ($dist_stmt->fetchAll() as $row) $rating_dist[$row['rating']] = $row['cnt'];

// Check if saved
$is_saved = false;
if (is_logged_in() && current_role() === 'student') {
    $sv = $pdo->prepare("SELECT id FROM saved_pgs WHERE student_id = ? AND pg_id = ?");
    $sv->execute([current_user_id(), $pg_id]);
    $is_saved = (bool)$sv->fetch();
}

$amenities_map = [
    'has_wifi'    => ['icon' => 'fa-wifi',      'label' => 'Wi-Fi'],
    'has_ac'      => ['icon' => 'fa-snowflake',  'label' => 'Air Conditioning'],
    'has_food'    => ['icon' => 'fa-utensils',   'label' => 'Meals Included'],
    'has_parking' => ['icon' => 'fa-parking',    'label' => 'Parking'],
    'has_laundry' => ['icon' => 'fa-tshirt',     'label' => 'Laundry'],
    'has_gym'     => ['icon' => 'fa-dumbbell',   'label' => 'Gym'],
    'has_cctv'    => ['icon' => 'fa-video',      'label' => 'CCTV Security'],
    'has_warden'  => ['icon' => 'fa-user-shield','label' => 'Warden'],
    'has_transport'  => ['icon' => 'fa-bus','label' => 'Pick and Drop'],
];

$total_beds_all    = array_sum(array_column($room_types, 'total_beds'));
$available_beds_all= array_sum(array_column($room_types, 'available_beds'));
?>
<!DOCTYPE html>
<html lang="en" data-base-url="<?= BASE_URL ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pg['title']) ?> — MUJSTAYS</title>
  <meta name="description" content="<?= htmlspecialchars(truncate($pg['description'], 155)) ?>">
  <meta property="og:title" content="<?= htmlspecialchars($pg['title']) ?>">
  <meta property="og:description" content="<?= htmlspecialchars(truncate($pg['description'], 155)) ?>">
  <meta name="csrf-token" content="<?= csrf_token() ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script type="application/ld+json">{"@context":"https://schema.org","@type":"LodgingBusiness","name":"<?= addslashes($pg['title']) ?>","address":{"@type":"PostalAddress","streetAddress":"<?= addslashes($pg['address']) ?>"},"priceRange":"₹<?= $pg['price_min'] ?>-₹<?= $pg['price_max'] ?>","aggregateRating":{"@type":"AggregateRating","ratingValue":"<?= $pg['avg_rating'] ?>","reviewCount":"<?= $pg['total_reviews'] ?>"}}</script>
</head>
<body>
<?php require_once 'components/navbar.php'; ?>

<!-- Breadcrumb -->
<div style="background:var(--bg2);padding:12px 0;border-bottom:1px solid var(--border)">
  <div class="container">
    <div class="breadcrumb" style="color:var(--text-muted)">
      <a href="<?= BASE_URL ?>" style="color:var(--text-muted)">Home</a>
      <span class="breadcrumb-sep">›</span>
      <a href="<?= BASE_URL ?>/explore.php" style="color:var(--text-muted)">Explore PGs</a>
      <span class="breadcrumb-sep">›</span>
      <span style="color:var(--primary)"><?= htmlspecialchars(truncate($pg['title'], 50)) ?></span>
    </div>
  </div>
</div>

<div class="section-sm">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 350px;gap:32px;align-items:start">

      <!-- LEFT COLUMN -->
      <div>
        <!-- Gallery -->
        <div class="gallery-main" style="margin-bottom:8px">
          <?php $main_img = !empty($images) ? (BASE_URL . '/' . ltrim($images[0]['file_path'], '/')) : (BASE_URL . '/assets/images/pg-placeholder.jpg'); ?>
          <img src="<?= $main_img ?>" alt="<?= htmlspecialchars($pg['title']) ?>" id="gallery-main-img">
        </div>
        <?php if (count($images) > 1): ?>
        <div class="gallery-thumbs">
          <?php foreach ($images as $i => $img): ?>
          <div class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>" onclick="switchImage(this,'<?= BASE_URL . '/' . ltrim($img['file_path'], '/') ?>')">
            <img src="<?= BASE_URL . '/' . ltrim($img['file_path'], '/') ?>" alt="Photo <?= $i+1 ?>">
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Heading -->
        <div style="margin-top:28px;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px">
          <div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px">
              <?= gender_badge($pg['gender_preference']) ?>
              <?php if ($pg['is_kyc_verified']): ?>
                <span class="badge badge-success"><i class="fas fa-shield-alt"></i> KYC Verified Owner</span>
              <?php endif; ?>
              <?php if ($pg['is_featured']): ?>
                <span class="badge badge-primary">⭐ Featured</span>
              <?php endif; ?>
            </div>
            <h1 style="font-size:1.7rem;margin-bottom:8px"><?= htmlspecialchars($pg['title']) ?></h1>
            <div style="color:var(--text-muted);font-size:14px;display:flex;align-items:center;gap:8px">
              <i class="fas fa-map-marker-alt" style="color:var(--accent)"></i>
              <?= htmlspecialchars($pg['address']) ?>
            </div>
            <?php if ($pg['distance_from_muj']): ?>
            <div style="margin-top:6px;font-size:13px;color:var(--accent);font-weight:600">
              <i class="fas fa-route"></i> <?= format_distance((float)$pg['distance_from_muj']) ?>
            </div>
            <?php endif; ?>
          </div>
          <div style="text-align:right">
            <div style="font-size:2rem;font-weight:800;color:var(--primary)"><?= format_currency($pg['price_min']) ?></div>
            <div style="font-size:13px;color:var(--text-muted)">onwards/month</div>
            <div style="display:flex;align-items:center;gap:6px;margin-top:8px;justify-content:flex-end">
              <?php $rating = (float)$pg['avg_rating']; $mode = 'display'; $size = 'normal'; require 'components/star-rating.php'; ?>
              <span style="font-weight:600;color:var(--primary)"><?= number_format($rating,1) ?></span>
              <span style="color:var(--text-muted);font-size:13px">(<?= $pg['total_reviews'] ?> reviews)</span>
            </div>
          </div>
        </div>

        <!-- Availability Banner -->
        <div style="margin:20px 0;padding:14px 18px;border-radius:10px;background:<?= $available_beds_all > 0 ? '#EAFAF1' : '#FEF9E7' ?>;border-left:4px solid <?= $available_beds_all > 0 ? 'var(--success)' : 'var(--warning)' ?>;display:flex;align-items:center;gap:10px">
          <i class="fas fa-<?= $available_beds_all > 0 ? 'check-circle' : 'exclamation-circle' ?>" style="color:<?= $available_beds_all > 0 ? 'var(--success)' : 'var(--warning)' ?>;font-size:20px"></i>
          <div>
            <strong style="color:var(--primary)"><?= $available_beds_all > 0 ? "$available_beds_all Bed" . ($available_beds_all != 1 ? 's' : '') . " Available" : 'All Rooms Full' ?></strong>
            <span style="color:var(--text-muted);font-size:13px;margin-left:8px">out of <?= $total_beds_all ?> total beds</span>
          </div>
        </div>

        <!-- Room Types -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-header"><h3 style="margin:0;font-size:16px"><i class="fas fa-bed" style="color:var(--accent)"></i> Room Types & Pricing</h3></div>
          <div class="table-wrap" style="border:none;border-radius:0">
            <table>
              <thead><tr><th>Type</th><th>Price/Month</th><th>Security Deposit</th><th>Availability</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($room_types as $rt): ?>
                <tr>
                  <td><strong><?= ucfirst($rt['type']) ?></strong><?php if ($rt['description']): ?><div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($rt['description']) ?></div><?php endif; ?></td>
                  <td style="font-weight:700;color:var(--primary)"><?= format_currency($rt['price_per_month']) ?></td>
                  <td><?= format_currency($rt['security_deposit']) ?></td>
                  <td>
                    <?php if ($rt['available_beds'] > 0): ?>
                      <span class="badge badge-success"><?= $rt['available_beds'] ?> of <?= $rt['total_beds'] ?> free</span>
                    <?php else: ?>
                      <span class="badge badge-danger">Full</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($rt['available_beds'] > 0 && is_logged_in() && current_role() === 'student'): ?>
                      <button class="btn btn-primary btn-sm" onclick="openModal('booking-modal')">Book Now</button>
                    <?php elseif (!is_logged_in()): ?>
                      <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline btn-sm">Login to Book</a>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Description -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-body">
            <h3 style="margin-bottom:16px"><i class="fas fa-info-circle" style="color:var(--accent)"></i> About This PG</h3>
            <p style="color:var(--text);line-height:1.8"><?= nl2br(htmlspecialchars($pg['description'])) ?></p>
          </div>
        </div>

        <!-- Amenities -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-body">
            <h3 style="margin-bottom:20px"><i class="fas fa-check-double" style="color:var(--accent)"></i> Amenities</h3>
            <div class="amenity-grid">
              <?php foreach ($amenities_map as $key => $info): 
                $has_am = (bool)($pg[$key] ?? 0);
              ?>
              <div class="amenity-pill <?= $has_am ? 'active' : '' ?>" style="padding:12px 14px;border:1px solid <?= $has_am ? 'var(--accent)' : 'var(--border)' ?>;border-radius:10px;opacity:<?= $has_am ? '1' : '.45' ?>">
                <i class="fas <?= $info['icon'] ?>" style="color:<?= $has_am ? 'var(--accent)' : 'var(--text-muted)' ?>"></i>
                <span style="font-size:13px;font-weight:500;color:<?= $has_am ? 'var(--text)' : 'var(--text-muted)' ?>"><?= $info['label'] ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Rules -->
        <?php if ($pg['rules']): ?>
        <div class="card" style="margin-bottom:24px">
          <div class="card-body">
            <h3 style="margin-bottom:16px"><i class="fas fa-list-ul" style="color:var(--accent)"></i> House Rules & Policies</h3>
            <p style="color:var(--text);line-height:1.8"><?= nl2br(htmlspecialchars($pg['rules'])) ?></p>
          </div>
        </div>
        <?php endif; ?>

        <!-- Map -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-body">
            <h3 style="margin-bottom:16px"><i class="fas fa-map-marked-alt" style="color:var(--accent)"></i> Location Map</h3>
            <iframe class="map-embed"
              src="https://maps.google.com/maps?q=<?= urlencode($pg['address']) ?>&output=embed&z=15"
              loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            <div style="margin-top:12px;font-size:13px;color:var(--text-muted)">
              <i class="fas fa-map-marker-alt" style="color:var(--accent)"></i>
              <?= htmlspecialchars($pg['address']) ?>
              <?php if ($pg['distance_from_muj']): ?>
                · <strong style="color:var(--accent)"><?= format_distance((float)$pg['distance_from_muj']) ?></strong>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Reviews -->
        <div class="card" style="margin-bottom:24px">
          <div class="card-body">
            <h3 style="margin-bottom:20px"><i class="fas fa-star" style="color:#F39C12"></i> Student Reviews (<?= $pg['total_reviews'] ?>)</h3>
            <?php if ($pg['total_reviews'] > 0): ?>
            <div style="display:grid;grid-template-columns:auto 1fr;gap:24px;margin-bottom:28px;align-items:center">
              <div style="text-align:center">
                <div style="font-size:4rem;font-weight:800;color:var(--primary);line-height:1"><?= number_format((float)$pg['avg_rating'],1) ?></div>
                <?php $rating = (float)$pg['avg_rating']; $mode = 'display'; $size = 'large'; require 'components/star-rating.php'; ?>
                <div style="font-size:13px;color:var(--text-muted);margin-top:4px"><?= $pg['total_reviews'] ?> reviews</div>
              </div>
              <div>
                <?php for ($s = 5; $s >= 1; $s--): $cnt = $rating_dist[$s] ?? 0; $pct = $pg['total_reviews'] > 0 ? ($cnt/$pg['total_reviews'])*100 : 0; ?>
                <div class="rating-bar" style="margin-bottom:6px">
                  <span style="font-size:12px;width:15px;text-align:right;color:var(--text-muted)"><?= $s ?></span>
                  <i class="fas fa-star" style="color:#F39C12;font-size:11px"></i>
                  <div class="rating-bar-track" style="flex:1"><div class="rating-bar-fill" style="width:<?= round($pct) ?>%"></div></div>
                  <span style="font-size:12px;width:20px;color:var(--text-muted)"><?= $cnt ?></span>
                </div>
                <?php endfor; ?>
              </div>
            </div>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
              <div class="empty-state" style="padding:40px 0">
                <i class="fas fa-comment-slash"></i>
                <p>No reviews yet. Be the first to review after your stay!</p>
              </div>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:20px">
                <?php foreach ($reviews as $rev): ?>
                <div style="padding:20px;background:var(--bg);border-radius:10px;border:1px solid var(--border)">
                  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px">
                    <div style="display:flex;align-items:center;gap:10px">
                      <div style="width:40px;height:40px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:700;font-size:14px">
                        <?= strtoupper(mb_substr($rev['student_name'],0,1)) ?>
                      </div>
                      <div>
                        <div style="font-weight:700;font-size:14px"><?= htmlspecialchars(explode(' ',$rev['student_name'])[0] . ' ' . (isset(explode(' ',$rev['student_name'])[1]) ? mb_substr(explode(' ',$rev['student_name'])[1],0,1).'.' : '')) ?></div>
                        <div style="font-size:12px;color:var(--text-muted)"><?= date('d M Y', strtotime($rev['created_at'])) ?></div>
                      </div>
                    </div>
                    <div class="stars" style="font-size:13px">
                      <?php for ($i=1;$i<=5;$i++) echo '<i class="'.($i<=$rev['rating']?'fas':'far').' fa-star'.($i>$rev['rating']?' style=\"color:var(--border)\"':'').'"></i>'; ?>
                    </div>
                  </div>
                  <?php if ($rev['review_text']): ?>
                    <p style="color:var(--text);font-size:14px;margin-bottom:0;line-height:1.7"><?= htmlspecialchars($rev['review_text']) ?></p>
                  <?php endif; ?>
                  <?php if ($rev['owner_response']): ?>
                    <div style="margin-top:14px;padding:12px;background:#fff;border-radius:8px;border-left:3px solid var(--accent)">
                      <div style="font-size:12px;font-weight:700;color:var(--accent);margin-bottom:4px">🏠 Owner Response</div>
                      <p style="font-size:13px;color:var(--text-muted);margin:0"><?= htmlspecialchars($rev['owner_response']) ?></p>
                    </div>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN (Sticky sidebar) -->
      <div style="position:sticky;top:88px;display:flex;flex-direction:column;gap:16px">

        <!-- Book Now Card -->
        <div class="card">
          <div class="card-body">
            <div style="text-align:center;margin-bottom:16px">
              <div style="font-size:2rem;font-weight:800;color:var(--primary)"><?= format_currency($pg['price_min']) ?></div>
              <div style="color:var(--text-muted);font-size:13px">per month onwards</div>
            </div>
            <?php if ($available_beds_all > 0): ?>
              <?php if (is_logged_in() && current_role() === 'student'): ?>
                <button class="btn btn-primary btn-xl btn-w100" onclick="openModal('booking-modal')">
                  <i class="fas fa-calendar-check"></i> Book Now
                </button>
              <?php elseif (!is_logged_in()): ?>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-xl btn-w100">
                  <i class="fas fa-sign-in-alt"></i> Login to Book
                </a>
              <?php endif; ?>
            <?php else: ?>
              <button class="btn btn-warning btn-w100" disabled>All Rooms Full</button>
            <?php endif; ?>

            <!-- Save button -->
            <?php if (is_logged_in() && current_role() === 'student'): ?>
            <button class="btn btn-outline btn-w100 save-btn <?= $is_saved ? 'saved' : '' ?>" data-pg="<?= $pg_id ?>" style="margin-top:10px;color:<?= $is_saved ? 'var(--danger)' : 'var(--primary)' ?>">
              <?= $is_saved ? '♥ Saved' : '♡ Save for Later' ?>
            </button>
            <?php endif; ?>
          </div>
        </div>

        <!-- Owner Info Card -->
        <div class="card">
          <div class="card-body">
            <h4 style="margin-bottom:16px">Property Owner</h4>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
              <div style="width:50px;height:50px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:50%;display:grid;place-items:center;color:#fff;font-size:20px;font-weight:700;flex-shrink:0">
                <?= strtoupper(mb_substr($pg['owner_name'],0,1)) ?>
              </div>
              <div>
                <div style="font-weight:700;color:var(--primary)"><?= htmlspecialchars($pg['owner_name']) ?></div>
                <?php if ($pg['is_kyc_verified']): ?>
                  <span class="badge badge-success" style="font-size:11px"><i class="fas fa-shield-alt"></i> KYC Verified</span>
                <?php else: ?>
                  <span class="badge badge-warning" style="font-size:11px">Pending Verification</span>
                <?php endif; ?>
              </div>
            </div>

            <div style="margin-bottom:12px;font-size:14px">
              <i class="fas fa-phone" style="color:var(--accent);width:18px"></i>
              <?php if (is_logged_in()): ?>
                <span id="phone-display" style="cursor:pointer;color:var(--accent)" onclick="revealPhone(<?= $pg_id ?>)">
                  📞 Click to reveal number
                </span>
              <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" style="color:var(--accent)">Login to see contact</a>
              <?php endif; ?>
            </div>

            <?php if (is_logged_in()): ?>
            <a href="<?= BASE_URL ?>/user/chat.php?with=<?= $pg['owner_user_id'] ?>&pg_id=<?= $pg_id ?>" class="btn btn-outline-accent btn-w100" style="margin-bottom:8px">
              <i class="fas fa-comment"></i> Chat with Owner
            </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/contact.php?report_pg=<?= $pg_id ?>" style="font-size:12px;color:var(--text-muted);display:block;text-align:center;margin-top:8px">
              <i class="fas fa-flag"></i> Report this listing
            </a>
          </div>
        </div>

        <!-- Distance from MUJ -->
        <div class="card">
          <div class="card-body" style="text-align:center">
            <i class="fas fa-university" style="font-size:28px;color:var(--accent);margin-bottom:10px"></i>
            <div style="font-weight:700;color:var(--primary);font-size:18px"><?= $pg['distance_from_muj'] ? format_distance((float)$pg['distance_from_muj']) : 'Distance unknown' ?></div>
            <div style="font-size:13px;color:var(--text-muted);margin-top:4px">from MUJ Main Gate</div>
            <button onclick="startNavigation(<?= $pg['latitude'] ?>, <?= $pg['longitude'] ?>)" id="directionsBtn" class="btn btn-outline btn-sm" style="margin-top:12px; width:100%;">
              <i class="fas fa-directions"></i> Get Real-Time Directions
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Booking Modal -->
<?php require_once 'components/booking-modal.php'; ?>

<!-- Lightbox -->
<div id="lightbox" class="lightbox">
  <button class="lightbox-close" id="lb-close">✕</button>
  <img src="" alt="Gallery" id="lb-img">
</div>

<!-- Sticky mobile CTA -->
<div style="display:none;position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid var(--border);padding:12px 16px;z-index:700;align-items:center;justify-content:space-between" id="mobile-cta">
  <div>
    <div style="font-weight:800;color:var(--primary);font-size:18px"><?= format_currency($pg['price_min']) ?>/mo</div>
    <div style="font-size:12px;color:var(--text-muted)">onwards</div>
  </div>
  <button class="btn btn-primary btn-lg" onclick="openModal('booking-modal')">Book Now</button>
</div>

<?php require_once 'components/footer.php'; ?>

<script>
var BASE_URL = '<?= BASE_URL ?>';
function switchImage(el, src) {
  document.getElementById('gallery-main-img').src = src;
  document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('lb-img').src = src;
}
function revealPhone(pgId) {
  fetch(BASE_URL + '/api/reveal-phone.php?pg_id=' + pgId, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
    .then(r => r.json()).then(d => {
      if (d.phone) document.getElementById('phone-display').innerHTML = '<a href="tel:' + d.phone + '" style="color:var(--success)">' + d.phone + '</a>';
    });
}
// Mobile sticky CTA - show after scrolling past gallery
window.addEventListener('scroll', () => {
  const cta = document.getElementById('mobile-cta');
  if (window.innerWidth <= 768) cta.style.display = window.scrollY > 400 ? 'flex' : 'none';
});
document.getElementById('lb-close')?.addEventListener('click', () => document.getElementById('lightbox').classList.remove('open'));
document.getElementById('gallery-main-img')?.addEventListener('click', () => {
  document.getElementById('lb-img').src = document.getElementById('gallery-main-img').src;
  document.getElementById('lightbox').classList.add('open');
});

function startNavigation(destLat, destLng) {
  const btn = document.getElementById('directionsBtn');
  const originalHtml = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Locating you...';
  
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const originLat = position.coords.latitude;
        const originLng = position.coords.longitude;
        btn.innerHTML = originalHtml;
        window.open(`https://www.google.com/maps/dir/?api=1&origin=${originLat},${originLng}&destination=${destLat},${destLng}`, '_blank');
      },
      (error) => {
        alert("Couldn't retrieve your location. Falling back to default navigation.");
        btn.innerHTML = originalHtml;
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${destLat},${destLng}`, '_blank');
      },
      { enableHighAccuracy: true, timeout: 5000 }
    );
  } else {
    alert("Geolocation is not supported by your browser.");
    window.open(`https://www.google.com/maps/dir/?api=1&destination=${destLat},${destLng}`, '_blank');
  }
}
</script>
<script src="<?= BASE_URL ?>/assets/js/main.js?v=<?= time() ?>"></script>
</body>
</html>
