<?php
// components/footer.php — Global footer
require_once __DIR__ . '/../config/config.php';
?>
<footer class="footer">
  <!-- Floating Compare Bar (for Students) -->
  <div id="compare-bar" class="compare-bar" style="display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;background:linear-gradient(135deg,var(--primary),var(--accent));color:#fff;padding:12px 24px;border-radius:999px;box-shadow:var(--shadow-lg);align-items:center;gap:16px;animation:slideUp .3s;">
    <div style="font-weight:700;font-size:14px;"><span id="compare-count">0</span> PGs Added to Compare</div>
    <div style="width:1px;height:24px;background:rgba(255,255,255,.2);"></div>
    <a href="#" id="compare-link" class="btn btn-sm btn-accent" style="border:none;background:#fff;color:var(--primary)!important;">Compare Now &rarr;</a>
    <button onclick="sessionStorage.removeItem('compareList'); location.reload();" class="btn btn-ghost btn-sm" style="color:#fff!important;padding:0;min-width:auto;font-size:18px;" title="Clear">&times;</button>
  </div>

  <div class="container">
    <div class="footer-grid">
      <!-- Brand -->
      <div class="footer-brand">
        <h3>🏠 MUJSTAYS</h3>
        <p>The only PG discovery & booking platform built exclusively for Manipal University Jaipur students. Find, compare, and book verified PGs — all in one place.</p>
        <div class="footer-social">
          <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <a href="#" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <h4>Quick Links</h4>
        <div class="footer-links">
          <a href="<?= BASE_URL ?>/">Home</a>
          <a href="<?= BASE_URL ?>/explore.php">Explore PGs</a>
          <a href="<?= BASE_URL ?>/about.php">About Us</a>
          <a href="<?= BASE_URL ?>/contact.php">Contact</a>
          <a href="<?= BASE_URL ?>/faq.php">FAQ</a>
          <a href="<?= BASE_URL ?>/signup.php?role=owner">List Your PG</a>
        </div>
      </div>

      <!-- Legal -->
      <div>
        <h4>Legal</h4>
        <div class="footer-links">
          <a href="<?= BASE_URL ?>/terms.php">Terms & Conditions</a>
          <a href="<?= BASE_URL ?>/privacy.php">Privacy Policy</a>
          <a href="<?= BASE_URL ?>/contact.php">Report a Problem</a>
        </div>
        <h4 style="margin-top:24px">Top Categories</h4>
        <div class="footer-links">
          <a href="<?= BASE_URL ?>/explore.php?gender=male">Boys PGs</a>
          <a href="<?= BASE_URL ?>/explore.php?gender=female">Girls PGs</a>
          <a href="<?= BASE_URL ?>/explore.php?distance=0.5">Walking Distance</a>
          <a href="<?= BASE_URL ?>/explore.php?amenities[]=has_food">With Mess/Food</a>
        </div>
      </div>

      <!-- Contact Info -->
      <div>
        <h4>Contact Us</h4>
        <div class="footer-contact">
          <span><i class="fas fa-map-marker-alt" style="color:var(--accent)"></i>Near Manipal University Jaipur, Dehmi Kalan, Jaipur – 303007</span>
          <span><i class="fas fa-envelope" style="color:var(--accent)"></i> <a href="mailto:hello@mujstays.com" style="color:rgba(255,255,255,.65)">hello@mujstays.com</a></span>
          <span><i class="fas fa-phone" style="color:var(--accent)"></i> +91 98765 43210</span>
          <span><i class="fas fa-clock" style="color:var(--accent)"></i> Mon–Sat, 9 AM – 6 PM</span>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> MUJSTAYS. All rights reserved.</span>
      <span style="color:rgba(255,255,255,.4)">Built with ❤️ for MUJ Students, Jaipur</span>
      <div style="display:flex;gap:16px">
        <a href="<?= BASE_URL ?>/terms.php">Terms</a>
        <a href="<?= BASE_URL ?>/privacy.php">Privacy</a>
        <a href="<?= BASE_URL ?>/contact.php">Support</a>
      </div>
    </div>
  </div>
</footer>
