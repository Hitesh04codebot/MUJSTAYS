<?php
// components/pg-card.php — Reusable PG listing card
// Expects $pg array with listing data

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($pg)) return;

$is_logged  = !empty($_SESSION['user_id']);
$role       = $_SESSION['role'] ?? '';
$is_student = $role === 'student';

// Check if saved by this student
$is_saved = false;
if ($is_student) {
    $pdo_card = get_db();
    $stmt = $pdo_card->prepare("SELECT id FROM saved_pgs WHERE student_id = ? AND pg_id = ?");
    $stmt->execute([$_SESSION['user_id'], $pg['id']]);
    $is_saved = (bool)$stmt->fetch();
}

// Cover image
$cover = get_asset_url($pg['cover_image'] ?? null, 'assets/images/pg-placeholder.jpg');

// Amenity pills (show up to 4)
$amenities_icons = [
  'has_wifi'    => ['icon' => '<i class="fas fa-wifi"></i>', 'label' => 'WiFi'],
  'has_ac'      => ['icon' => '❄️', 'label' => 'AC'],
  'has_food'    => ['icon' => '🍽️', 'label' => 'Food'],
  'has_parking' => ['icon' => '🅿️', 'label' => 'Parking'],
  'has_laundry' => ['icon' => '👕', 'label' => 'Laundry'],
  'has_gym'     => ['icon' => '💪', 'label' => 'Gym'],
  'has_cctv'    => ['icon' => '📷', 'label' => 'CCTV'],
  'has_warden'  => ['icon' => '👮', 'label' => 'Warden'],
  'has_transport'  => ['icon' => '🚐', 'label' => 'Pick & Drop'],
];
$active_amenities = [];
foreach ($amenities_icons as $key => $info) {
  if (!empty($pg[$key])) $active_amenities[] = $info;
}
$show_amenities = array_slice($active_amenities, 0, 4);
$extra_count    = max(0, count($active_amenities) - 4);

// Rating stars
$rating = (float)($pg['avg_rating'] ?? 0);
$full_stars = floor($rating);
$half_star  = ($rating - $full_stars) >= 0.5;
?>
<div class="pg-card card-hover" style="display:flex; flex-direction:column; height:100%;">
  <div class="pg-card-img" style="position:relative; border-bottom:1px solid var(--border); background:#f1f5f9;">
    <!-- Fallback if image fails to load -->
    <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($pg['title']) ?>" loading="lazy" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&q=80&w=600';">

    <!-- Save button (students only) -->
    <?php if ($is_student): ?>
    <button class="pg-card-save save-btn <?= $is_saved ? 'saved' : '' ?>"
            data-pg="<?= $pg['id'] ?>"
            title="<?= $is_saved ? 'Remove from saved' : 'Save this PG' ?>"
            aria-label="Save PG" style="box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
      <?= $is_saved ? '♥' : '♡' ?>
    </button>
    <?php endif; ?>

    <!-- Badges row -->
    <div class="pg-card-badge-row" style="position:absolute; bottom:12px; left:12px; display:flex; gap:6px; flex-wrap:wrap; z-index:2;">
      <?= gender_badge($pg['gender_preference'] ?? 'any') ?>
      <?php if (!empty($pg['is_featured'])): ?>
        <span class="badge" style="background: linear-gradient(135deg, #FFD700, #F39C12); color:#000; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">⭐ Featured</span>
      <?php endif; ?>
    </div>
    <!-- Simple gradient overlay for readability -->
    <div style="position:absolute; bottom:0; left:0; right:0; height:60px; background:linear-gradient(to top, rgba(0,0,0,0.5), transparent); z-index:1;"></div>
  </div>

  <div class="pg-card-body" style="flex:1; display:flex; flex-direction:column; padding:20px;">
    <div class="pg-card-title" title="<?= htmlspecialchars($pg['title']) ?>" style="font-size:16px; margin-bottom:6px;">
      <?= htmlspecialchars(truncate($pg['title'], 60)) ?>
    </div>

    <div class="pg-card-location" style="font-size:13px; margin-bottom:12px;">
      <i class="fas fa-map-marker-alt" style="color:var(--accent); margin-right:4px;"></i><?= htmlspecialchars($pg['area_name']) ?>
      <?php if (!empty($pg['distance_from_muj'])): ?>
        <span style="color:var(--text-light); margin:0 4px;">&bull;</span><span style="color:var(--accent); font-weight:600;"><?= format_distance((float)$pg['distance_from_muj']) ?></span>
      <?php endif; ?>
    </div>

    <!-- Rating -->
    <div style="display:flex; align-items:center; gap:6px; margin-bottom:16px; background:#f8fafc; padding:6px 10px; border-radius:6px; width:fit-content; border:1px solid var(--border);">
      <div class="stars" style="font-size:12px; transform:translateY(-1px);">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <?php if ($i <= $full_stars): ?>
            <i class="fas fa-star" style="color:#F39C12;"></i>
          <?php elseif ($i == $full_stars + 1 && $half_star): ?>
            <i class="fas fa-star-half-alt" style="color:#F39C12;"></i>
          <?php else: ?>
            <i class="far fa-star" style="color:#cbd5e1;"></i>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
      <span style="font-size:13px; font-weight:700; color:var(--primary);"><?= number_format($rating, 1) ?></span>
      <span style="font-size:12px; color:var(--text-muted);">(&nbsp;<?= $pg['total_reviews'] ?? 0 ?>&nbsp;)</span>
    </div>

    <!-- Amenities -->
    <?php if (!empty($show_amenities)): ?>
    <div class="pg-card-amenities" style="margin-top:auto; display:flex; gap:6px; flex-wrap:wrap;">
      <?php foreach ($show_amenities as $a): ?>
        <span class="amenity-pill active" style="font-size:11px; padding:4px 8px; background:#EBF5FB; border:none; box-shadow:0 1px 2px rgba(0,0,0,0.02);"><?= $a['icon'] ?> <?= $a['label'] ?></span>
      <?php endforeach; ?>
      <?php if ($extra_count > 0): ?>
        <span class="amenity-pill tooltip" title="More amenities available" style="font-size:11px; padding:4px 8px; background:#f1f5f9; border:none;">+<?= $extra_count ?></span>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="pg-card-footer" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; padding:16px 20px; background:#fbfcfe; border-top:1px solid #edf2f7; gap:12px;">
    <div style="flex-shrink:0;">
      <div style="font-size:12px; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:2px;">Starts at</div>
      <div class="pg-card-price" style="font-size:18px; color:var(--primary); font-weight:800; line-height:1;">
        <?= format_currency($pg['price_min']) ?>
        <span style="font-size:12px; font-weight:500; color:var(--text-light);">/mo</span>
      </div>
    </div>
    <div style="display:flex; gap:8px; align-items:center;">
      <?php if ($is_student): ?>
        <button class="btn btn-ghost btn-icon compare-btn" data-pg="<?= $pg['id'] ?>" title="Compare" style="background:#fff; border:1px solid var(--border); color:var(--text-muted) !important;"><i class="fas fa-balance-scale"></i></button>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/pg-detail.php?id=<?= $pg['id'] ?>" class="btn btn-primary btn-sm" style="padding:8px 20px; font-weight:600; box-shadow:0 4px 10px rgba(46,134,171,0.25);">View <i class="fas fa-arrow-right" style="margin-left:4px; font-size:11px;"></i></a>
    </div>
  </div>
</div>
