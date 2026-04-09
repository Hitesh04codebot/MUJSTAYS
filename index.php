<?php
// index.php — MUJSTAYS Home Page
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_once 'includes/helpers.php';

// ---- Fetch data ----
// Featured PGs
$featured_stmt = $pdo->query("
    SELECT p.*, a.name AS area_name,
           (SELECT file_path FROM pg_images WHERE pg_id = p.id AND is_cover = 1 LIMIT 1) AS cover_image
    FROM pg_listings p
    JOIN areas a ON p.area_id = a.id
    WHERE p.status = 'approved' AND p.is_featured = 1 AND p.is_deleted = 0
    ORDER BY p.avg_rating DESC LIMIT 8
");
$featured_pgs = $featured_stmt->fetchAll();

// PG counts by area
$area_stmt = $pdo->query("
    SELECT a.name, COUNT(p.id) AS pg_count
    FROM areas a
    LEFT JOIN pg_listings p ON p.area_id = a.id AND p.status = 'approved' AND p.is_deleted = 0
    GROUP BY a.id ORDER BY pg_count DESC LIMIT 6
");
$area_counts = $area_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Stats
$stats_stmt = $pdo->query("
    SELECT
      (SELECT COUNT(*) FROM pg_listings WHERE status='approved' AND is_deleted=0) AS total_pgs,
      (SELECT COUNT(*) FROM users WHERE role='owner' AND is_kyc_verified=1) AS verified_owners,
      (SELECT COUNT(*) FROM bookings WHERE status IN ('confirmed','completed')) AS happy_students,
      (SELECT COUNT(*) FROM areas) AS areas
");
$stats = $stats_stmt->fetch();

// Testimonials from approved reviews
$reviews_stmt = $pdo->query("
    SELECT r.rating, r.review_text, r.created_at,
           u.name AS student_name, u.profile_photo,
           p.title AS pg_title
    FROM reviews r
    JOIN users u ON u.id = r.student_id
    JOIN pg_listings p ON p.id = r.pg_id
    WHERE r.is_approved = 1 AND r.rating >= 4 AND r.review_text IS NOT NULL
    ORDER BY r.rating DESC, r.created_at DESC LIMIT 6
");
$testimonials = $reviews_stmt->fetchAll();

// Recently added PGs
$recent_stmt = $pdo->query("
    SELECT p.*, a.name AS area_name,
           (SELECT file_path FROM pg_images WHERE pg_id = p.id AND is_cover = 1 LIMIT 1) AS cover_image
    FROM pg_listings p
    JOIN areas a ON p.area_id = a.id
    WHERE p.status = 'approved' AND p.is_deleted = 0
    ORDER BY p.created_at DESC LIMIT 6
");
$recent_pgs = $recent_stmt->fetchAll();

// Fetch full areas list from DB
$areas_db = $pdo->query("SELECT * FROM areas ORDER BY name ASC")->fetchAll();
$areas_icons = [
  'Jagatpura'  => '🏫', 'Govindpura' => '🌆', 'Sitapura' => '🏭', 
  'Tonk Road'  => '🛣️', 'Agra Road' => '🚌', 'Vatika' => '🌿'
];
?>
<!DOCTYPE html>
<html lang="en" data-base-url="<?= BASE_URL ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MUJSTAYS — Find Your Perfect PG Near Manipal University Jaipur</title>
  <meta name="description" content="MUJSTAYS is the #1 PG discovery and booking platform for MUJ students. Browse 500+ verified PGs near Manipal University Jaipur with photos, prices, and reviews.">
  <meta property="og:title" content="MUJSTAYS — PG Booking for MUJ Students">
  <meta property="og:description" content="Find, compare, and book verified PGs near Manipal University Jaipur.">
  <meta name="csrf-token" content="<?= csrf_token() ?>">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>

<?php require_once 'components/navbar.php'; ?>

<!-- ============ HERO ============ -->
<section class="hero">
  <div class="container">
    <div class="hero-content slide-up">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border-radius:999px;padding:6px 16px;margin-bottom:20px;font-size:13px;color:rgba(255,255,255,.9)">
        <span>🎓</span> Built exclusively for MUJ Students
      </div>
      <h1>Find Your Perfect PG<br><span>Near MUJ Campus</span></h1>
      <p>Discover verified PGs with real photos, genuine reviews, and hassle-free online booking — all within commuting distance of Manipal University Jaipur.</p>

      <!-- Search Bar -->
      <!-- Explore CTA -->
      <div style="margin-top: 32px; margin-bottom: 32px;">
        <a href="<?= BASE_URL ?>/explore.php" class="btn btn-primary btn-xl" style="background: linear-gradient(135deg, #FFD700, #F39C12); color:#000 !important; font-weight:800; border:none; box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4); padding: 18px 48px; font-size: 18px; border-radius: 999px;">Explore Verified PGs <i class="fas fa-arrow-right" style="margin-left:8px;"></i></a>
      </div>

      <!-- Hero Stats -->
      <div class="hero-stats">
        <div>
          <div class="hero-stat-num stat-counter" data-target="<?= $stats['total_pgs'] ?>" data-suffix="+"><?= $stats['total_pgs'] ?>+</div>
          <div class="hero-stat-label">Verified PGs</div>
        </div>
        <div>
          <div class="hero-stat-num stat-counter" data-target="<?= $stats['verified_owners'] ?>" data-suffix=""><?= $stats['verified_owners'] ?></div>
          <div class="hero-stat-label">KYC-Verified Owners</div>
        </div>
        <div>
          <div class="hero-stat-num stat-counter" data-target="<?= $stats['happy_students'] ?>" data-suffix="+"><?= $stats['happy_students'] ?>+</div>
          <div class="hero-stat-label">Happy Students</div>
        </div>
        <div>
          <div class="hero-stat-num stat-counter" data-target="<?= $stats['areas'] ?>" data-suffix=""><?= $stats['areas'] ?></div>
          <div class="hero-stat-label">Areas Covered</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ QUICK CATEGORIES ============ -->
<section class="section-sm" style="background:var(--bg2)">
  <div class="container">
    <div class="section-title">
      <div class="accent-line"></div>
      <h2>Find Your Perfect Fit</h2>
      <p>Quickly browse PGs that match your specific needs near MUJ</p>
    </div>
    <div class="location-cards" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
      <a href="<?= BASE_URL ?>/explore.php?gender=male" class="location-card" style="text-decoration:none; background: linear-gradient(135deg, #2E86AB, #215a76);">
        <div style="font-size:32px;margin-bottom:10px">👦</div>
        <h3>Boys PGs</h3>
        <p>Verified male-only accommodations</p>
        <div style="margin-top:12px;font-size:13px;color:rgba(255,255,255,.9);font-weight:600">View All →</div>
      </a>
      <a href="<?= BASE_URL ?>/explore.php?gender=female" class="location-card" style="text-decoration:none; background: linear-gradient(135deg, #D81B60, #ad1457);">
        <div style="font-size:32px;margin-bottom:10px">👧</div>
        <h3>Girls PGs</h3>
        <p>Secure female-only accommodations</p>
        <div style="margin-top:12px;font-size:13px;color:rgba(255,255,255,.9);font-weight:600">View All →</div>
      </a>
      <a href="<?= BASE_URL ?>/explore.php?distance=0.5" class="location-card" style="text-decoration:none; background: linear-gradient(135deg, #27AE60, #1e8449);">
        <div style="font-size:32px;margin-bottom:10px">🏃</div>
        <h3>Walking Distance</h3>
        <p>Within 500m of MUJ Main Gate</p>
        <div style="margin-top:12px;font-size:13px;color:rgba(255,255,255,.9);font-weight:600">View All →</div>
      </a>
      <a href="<?= BASE_URL ?>/explore.php?amenities[]=has_food" class="location-card" style="text-decoration:none; background: linear-gradient(135deg, #F39C12, #d68910);">
        <div style="font-size:32px;margin-bottom:10px">🍱</div>
        <h3>With Food/Mess</h3>
        <p>Includes daily healthy meals</p>
        <div style="margin-top:12px;font-size:13px;color:rgba(255,255,255,.9);font-weight:600">View All →</div>
      </a>
    </div>
  </div>
</section>

<!-- ============ FEATURED PGS ============ -->
<?php if (!empty($featured_pgs)): ?>
<section class="section">
  <div class="container">
    <div class="section-title">
      <div class="accent-line"></div>
      <h2>Featured PGs</h2>
      <p>Handpicked, premium PGs recommended by the MUJSTAYS team</p>
    </div>
    <div class="pg-grid">
      <?php foreach ($featured_pgs as $pg): ?>
        <?php require 'components/pg-card.php'; ?>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:40px">
      <a href="<?= BASE_URL ?>/explore.php" class="btn btn-outline-accent btn-lg">
        <i class="fas fa-th-large"></i> View All PGs
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ HOW IT WORKS ============ -->
<section class="section" style="background:var(--bg2)">
  <div class="container">
    <div class="section-title">
      <div class="accent-line"></div>
      <h2>How MUJSTAYS Works</h2>
      <p>Finding your perfect PG in Jaipur has never been easier</p>
    </div>
    <div class="steps">
      <div class="step">
        <div class="step-icon">🔍</div>
        <h3>Search & Filter</h3>
        <p>Browse hundreds of verified PGs. Filter by area, budget, room type, gender preference, and amenities to find your perfect match.</p>
      </div>
      <div class="step">
        <div class="step-icon" style="background:linear-gradient(135deg,var(--accent),#27AE60)">📋</div>
        <h3>Compare & Book</h3>
        <p>Compare up to 3 PGs side-by-side. Chat with owners directly. Book online with one click — no middlemen, no hidden fees.</p>
      </div>
      <div class="step">
        <div class="step-icon" style="background:linear-gradient(135deg,var(--success),#1ABC9C)">🏠</div>
        <h3>Move In & Review</h3>
        <p>Get a booking confirmation instantly. Move in on your chosen date and leave a review to help future MUJ students.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============ RECENTLY ADDED ============ -->
<?php if (!empty($recent_pgs)): ?>
<section class="section">
  <div class="container">
    <div class="section-title">
      <div class="accent-line"></div>
      <h2>Recently Added PGs</h2>
      <p>Fresh listings just added to the platform</p>
    </div>
    <div class="pg-grid">
      <?php foreach ($recent_pgs as $pg): ?>
        <?php require 'components/pg-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ STATS BAR ============ -->
<section class="stats-section" style="background:linear-gradient(135deg,var(--primary),var(--accent));padding:60px 0">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:32px;text-align:center">
      <div>
        <div style="font-size:3rem;font-weight:800;color:#fff;font-family:var(--font-head)" class="stat-counter" data-target="<?= $stats['total_pgs'] ?>" data-suffix="+"><?= $stats['total_pgs'] ?>+</div>
        <div style="color:rgba(255,255,255,.7);margin-top:4px">PGs Listed</div>
      </div>
      <div>
        <div style="font-size:3rem;font-weight:800;color:#fff;font-family:var(--font-head)" class="stat-counter" data-target="<?= $stats['verified_owners'] ?>"><?= $stats['verified_owners'] ?></div>
        <div style="color:rgba(255,255,255,.7);margin-top:4px">Verified Owners</div>
      </div>
      <div>
        <div style="font-size:3rem;font-weight:800;color:#fff;font-family:var(--font-head)" class="stat-counter" data-target="<?= $stats['happy_students'] ?>" data-suffix="+"><?= $stats['happy_students'] ?>+</div>
        <div style="color:rgba(255,255,255,.7);margin-top:4px">Happy Residents</div>
      </div>
      <div>
        <div style="font-size:3rem;font-weight:800;color:#fff;font-family:var(--font-head)" class="stat-counter" data-target="<?= $stats['areas'] ?>"><?= $stats['areas'] ?></div>
        <div style="color:rgba(255,255,255,.7);margin-top:4px">Areas Covered</div>
      </div>
    </div>
  </div>
</section>

<!-- ============ TESTIMONIALS ============ -->
<?php if (!empty($testimonials)): ?>
<section class="section" style="background:var(--bg2)">
  <div class="container">
    <div class="section-title">
      <div class="accent-line"></div>
      <h2>What MUJ Students Say</h2>
      <p>Real reviews from verified student residents</p>
    </div>
    <div class="pg-grid">
      <?php foreach ($testimonials as $t): ?>
      <div class="testimonial-card">
        <div class="stars" style="margin-bottom:12px">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <i class="fas fa-star" style="<?= $i > $t['rating'] ? 'color:var(--border)' : '' ?>"></i>
          <?php endfor; ?>
        </div>
        <p class="testimonial-text">"<?= htmlspecialchars(truncate($t['review_text'], 150)) ?>"</p>
        <div class="testimonial-author">
          <div style="width:44px;height:44px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:50%;display:grid;place-items:center;color:#fff;font-weight:700;font-size:16px;flex-shrink:0">
            <?= strtoupper(mb_substr($t['student_name'], 0, 1)) ?>
          </div>
          <div>
            <div class="testimonial-name"><?= htmlspecialchars($t['student_name']) ?></div>
            <div class="testimonial-meta">MUJ Student · <?= htmlspecialchars(truncate($t['pg_title'], 30)) ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============ OWNER CTA ============ -->
<section class="section">
  <div class="container">
    <div class="cta-banner">
      <div style="font-size:48px;margin-bottom:16px">🏘️</div>
      <h2>Are You a PG Owner?</h2>
      <p>List your property on MUJSTAYS and reach 10,000+ MUJ students actively searching for accommodation. Free to list, no upfront costs.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= BASE_URL ?>/signup.php?role=owner" class="btn btn-accent btn-xl">
          <i class="fas fa-plus"></i> List Your PG Free
        </a>
        <a href="<?= BASE_URL ?>/about.php" class="btn btn-outline" style="color:#fff;border-color:rgba(255,255,255,.5)">
          Learn More
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once 'components/footer.php'; ?>


<script>data-base-url="<?= BASE_URL ?>"; var BASE_URL='<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
