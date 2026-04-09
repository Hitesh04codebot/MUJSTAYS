<?php
// components/filter-sidebar.php — Search/Filter panel for explore.php
require_once __DIR__ . '/../config/config.php';

$f_gender  = $_GET['gender']  ?? '';
$f_min     = (int)($_GET['min_price'] ?? 2000);
$f_max     = (int)($_GET['max_price'] ?? 20000);
$f_sort    = $_GET['sort']    ?? 'relevance';
$f_dist    = $_GET['distance'] ?? '';
$f_rooms   = (array)($_GET['room_type'] ?? []);
$f_amenities = (array)($_GET['amenities'] ?? []);

$amenity_opts = [
  'has_wifi'    => ['icon' => '📶', 'label' => 'WiFi'],
  'has_ac'      => ['icon' => '❄️', 'label' => 'Air Conditioning'],
  'has_food'    => ['icon' => '🍽️', 'label' => 'Meals Included'],
  'has_parking' => ['icon' => '🅿️', 'label' => 'Parking'],
  'has_laundry' => ['icon' => '👕', 'label' => 'Laundry'],
  'has_gym'     => ['icon' => '💪', 'label' => 'Gym'],
  'has_cctv'    => ['icon' => '📷', 'label' => 'CCTV Security'],
  'has_warden'  => ['icon' => '👮', 'label' => 'Warden'],
  'has_transport' => ['icon' => '🚐', 'label' => 'Pick and Drop'],
];
?>
<aside class="filter-sidebar" id="filter-sidebar">
  <form id="filter-form" method="GET" action="<?= BASE_URL ?>/explore.php">

    <div class="filter-title">
      <span><i class="fas fa-filter" style="color:var(--accent)"></i> Filters</span>
      <a href="<?= BASE_URL ?>/explore.php" style="font-size:12px;color:var(--danger);font-weight:600">Clear All</a>
    </div>

    <!-- Search (text) -->
    <div class="filter-section">
      <div class="filter-section-title">Search</div>
      <div class="input-group">
        <i class="fas fa-search input-icon"></i>
        <input type="text" name="q" class="form-control" placeholder="Property name..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      </div>
    </div>

    <!-- Price Range -->
    <div class="filter-section">
      <div class="filter-section-title">Monthly Rent</div>
      <div style="display:flex;justify-content:space-between;margin-bottom:10px;font-size:13px;font-weight:600;color:var(--primary)">
        <span id="price-min-label">₹<?= number_format($f_min) ?></span>
        <span id="price-max-label">₹<?= number_format($f_max) ?></span>
      </div>
      <input type="range" id="price-min" name="min_price" min="2000" max="20000" step="500" value="<?= $f_min ?>" style="width:100%;accent-color:var(--accent)">
      <input type="range" id="price-max" name="max_price" min="2000" max="20000" step="500" value="<?= $f_max ?>" style="width:100%;accent-color:var(--accent)">
    </div>

    <!-- Room Type -->
    <div class="filter-section">
      <div class="filter-section-title">Room Type</div>
      <div class="checkbox-group">
        <?php foreach (['single' => '🛏 Single', 'double' => '🛏🛏 Double', 'triple' => '🛏🛏🛏 Triple', 'dormitory' => '🏨 Dormitory'] as $val => $label): ?>
          <label class="checkbox-item">
            <input type="checkbox" name="room_type[]" value="<?= $val ?>"
                   <?= in_array($val, $f_rooms) ? 'checked' : '' ?>>
            <span><?= $label ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Gender -->
    <div class="filter-section">
      <div class="filter-section-title">Gender Preference</div>
      <div class="checkbox-group">
        <?php foreach (['any' => '⚥ Co-ed (Any)', 'male' => '♂ Boys Only', 'female' => '♀ Girls Only'] as $val => $label): ?>
          <label class="checkbox-item">
            <input type="radio" name="gender" value="<?= $val ?>"
                   <?= $f_gender === $val ? 'checked' : '' ?>>
            <span><?= $label ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Distance -->
    <div class="filter-section">
      <div class="filter-section-title">Distance from MUJ</div>
      <select name="distance" class="form-select">
        <option value="">Any Distance</option>
        <option value="0.5" <?= $f_dist === '0.5' ? 'selected' : '' ?>>Within 500m</option>
        <option value="1"   <?= $f_dist === '1'   ? 'selected' : '' ?>>Within 1 km</option>
        <option value="2"   <?= $f_dist === '2'   ? 'selected' : '' ?>>Within 2 km</option>
        <option value="5"   <?= $f_dist === '5'   ? 'selected' : '' ?>>Within 5 km</option>
      </select>
    </div>

    <!-- Amenities -->
    <div class="filter-section">
      <div class="filter-section-title">Amenities</div>
      <div class="checkbox-group" style="max-height:220px;overflow-y:auto">
        <?php foreach ($amenity_opts as $key => $info): ?>
          <label class="checkbox-item">
            <input type="checkbox" name="amenities[]" value="<?= $key ?>"
                   <?= in_array($key, $f_amenities) ? 'checked' : '' ?>>
            <span><?= $info['icon'] ?> <?= $info['label'] ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Sort -->
    <div class="filter-section">
      <div class="filter-section-title">Sort By</div>
      <select name="sort" class="form-select">
        <option value="relevance"  <?= $f_sort === 'relevance'  ? 'selected' : '' ?>>Relevance</option>
        <option value="price_asc"  <?= $f_sort === 'price_asc'  ? 'selected' : '' ?>>Price: Low to High</option>
        <option value="price_desc" <?= $f_sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
        <option value="rating"     <?= $f_sort === 'rating'     ? 'selected' : '' ?>>Rating: High to Low</option>
        <option value="newest"     <?= $f_sort === 'newest'     ? 'selected' : '' ?>>Newest First</option>
        <option value="distance"   <?= $f_sort === 'distance'   ? 'selected' : '' ?>>Distance: Nearest</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary btn-w100">
      <i class="fas fa-search"></i> Apply Filters
    </button>
  </form>
</aside>
