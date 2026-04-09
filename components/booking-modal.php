<?php
// components/booking-modal.php — Booking request modal
// Requires $pg (listing array) and $room_types (array of room_types for this pg)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!isset($pg) || !isset($room_types)) return;
$is_logged = !empty($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'student';
$min_date = date('Y-m-d', strtotime('+3 days'));
$csrf = csrf_token();
?>
<div class="modal-overlay" id="booking-modal">
  <div class="modal" style="max-width:540px">
    <button class="modal-close" onclick="closeModal('booking-modal')">&times;</button>
    <h2 class="modal-title">🏠 Book This PG</h2>
    <div style="background:var(--bg2);border-radius:10px;padding:14px;margin-bottom:20px">
      <div style="font-weight:700;color:var(--primary)"><?= htmlspecialchars($pg['title']) ?></div>
      <div style="font-size:13px;color:var(--text-muted)"><i class="fas fa-map-marker-alt" style="color:var(--accent)"></i> <?= htmlspecialchars($pg['area_name']) ?></div>
    </div>

    <?php if (!$is_logged): ?>
      <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        Please <a href="<?= BASE_URL ?>/login.php">log in</a> as a student to book this PG.
      </div>
    <?php else: ?>
    <form id="booking-form" method="POST" action="<?= BASE_URL ?>/api/book-pg.php">
      <?= csrf_field() ?>
      <input type="hidden" name="pg_id" value="<?= $pg['id'] ?>">

      <!-- Room Type Selection -->
      <div class="form-group">
        <label class="form-label">Select Room Type <span class="req">*</span></label>
        <div style="display:flex;flex-direction:column;gap:10px">
          <?php foreach ($room_types as $rt): ?>
            <?php $avail = (int)$rt['available_beds'] > 0; ?>
            <label style="display:flex;align-items:center;gap:12px;padding:14px;border:2px solid <?= $avail ? 'var(--border)' : '#eee' ?>;border-radius:10px;cursor:<?= $avail ? 'pointer' : 'not-allowed' ?>;transition:border-color .2s;opacity:<?= $avail ? '1' : '.55' ?>" class="room-option">
              <input type="radio" name="room_type_id" value="<?= $rt['id'] ?>"
                     data-price="<?= $rt['price_per_month'] ?>"
                     data-deposit="<?= $rt['security_deposit'] ?>"
                     <?= !$avail ? 'disabled' : '' ?> required>
              <div style="flex:1">
                <div style="font-weight:700;font-size:14px;color:var(--primary)"><?= ucfirst($rt['type']) ?> Room</div>
                <div style="font-size:12px;color:var(--text-muted)">
                  <?= format_currency($rt['price_per_month']) ?>/mo · Deposit: <?= format_currency($rt['security_deposit']) ?>
                </div>
              </div>
              <?php if ($avail): ?>
                <span class="badge badge-success"><?= $rt['available_beds'] ?> bed<?= $rt['available_beds'] != 1 ? 's' : '' ?> free</span>
              <?php else: ?>
                <span class="badge badge-danger">Full</span>
              <?php endif; ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Move-in Date -->
      <div class="form-group">
        <label class="form-label" for="move-in-date">Move-in Date <span class="req">*</span></label>
        <input type="date" class="form-control" id="move-in-date" name="move_in_date" min="<?= $min_date ?>" required>
      </div>

      <!-- Duration -->
      <div class="form-group">
        <label class="form-label" for="duration">Planned Stay Duration</label>
        <select id="duration" name="duration_months" class="form-select">
          <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>"><?= $m ?> month<?= $m > 1 ? 's' : '' ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <!-- Price Calculator -->
      <div style="background:linear-gradient(135deg,#EBF5FB,#F0F9FF);border-radius:10px;padding:16px;margin-bottom:20px;display:none" id="price-calc">
        <div style="font-size:13px;color:var(--text-muted);margin-bottom:6px" id="price-breakdown"></div>
        <div style="display:flex;justify-content:space-between;align-items:center">
          <strong style="color:var(--primary)">Total at move-in</strong>
          <strong style="font-size:22px;color:var(--primary)" id="total-price">—</strong>
        </div>
      </div>

      <!-- Payment Option -->
      <div class="form-group">
        <label class="form-label">Payment Option <span class="req">*</span></label>
        <div style="display:flex;flex-direction:column;gap:8px">
          <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;border:2px solid var(--border);border-radius:8px;cursor:pointer">
            <input type="radio" name="payment_option" value="offline" checked>
            <div>
              <div style="font-weight:600;font-size:14px">Pay at Property</div>
              <div style="font-size:12px;color:var(--text-muted)">No payment now — pay when you move in</div>
            </div>
          </label>
          <label style="display:flex;align-items:flex-start;gap:10px;padding:12px;border:2px solid var(--border);border-radius:8px;cursor:pointer">
            <input type="radio" name="payment_option" value="online">
            <div>
              <div style="font-weight:600;font-size:14px">Pay Advance Online <span class="badge badge-success" style="font-size:10px">Recommended</span></div>
              <div style="font-size:12px;color:var(--text-muted)">Instant booking confirmation with online advance payment</div>
            </div>
          </label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-xl btn-w100">
        <i class="fas fa-calendar-check"></i> Confirm Booking Request
      </button>
      <p style="font-size:12px;color:var(--text-muted);text-align:center;margin-top:12px">
        You won't be charged yet. The owner will review your request.
      </p>
    </form>
    <?php endif; ?>
  </div>
</div>

<script>
document.querySelectorAll('input[name="room_type_id"]').forEach(r => {
  r.addEventListener('change', updateBookingPrice);
});
document.getElementById('duration')?.addEventListener('input', updateBookingPrice);

function updateBookingPrice() {
  const sel = document.querySelector('input[name="room_type_id"]:checked');
  const dur = parseInt(document.getElementById('duration')?.value || 1);
  const calc = document.getElementById('price-calc');
  if (sel) {
    const price = parseInt(sel.dataset.price);
    const dep = parseInt(sel.dataset.deposit);
    const total = price * dur + dep;
    document.getElementById('price-breakdown').textContent = `Rent: ₹${(price * dur).toLocaleString('en-IN')} + Deposit: ₹${dep.toLocaleString('en-IN')}`;
    document.getElementById('total-price').textContent = '₹' + total.toLocaleString('en-IN');
    calc.style.display = 'block';
  }
}

// Highlight selected room option border
document.querySelectorAll('.room-option').forEach(lbl => {
  lbl.querySelector('input')?.addEventListener('change', () => {
    document.querySelectorAll('.room-option').forEach(l => l.style.borderColor = 'var(--border)');
    lbl.style.borderColor = 'var(--accent)';
  });
});

// Intercept form submission to use AJAX and graceful Toast UI
document.getElementById('booking-form')?.addEventListener('submit', function(e) {
  e.preventDefault();
  
  // Show loading state
  const btn = this.querySelector('button[type="submit"]');
  const ogText = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
  btn.disabled = true;

  fetch(this.action, {
    method: 'POST',
    body: new FormData(this),
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(res => {
    btn.innerHTML = ogText;
    btn.disabled = false;
    
    if (res.error) {
      if (typeof showToast === 'function') showToast(res.error, 'error');
      else alert(res.error);
    } else if (res.success) {
      if (typeof showToast === 'function') showToast(res.message, 'success');
      if (typeof closeModal === 'function') closeModal('booking-modal');
      
      // Redirect to bookings dashboard after a short delay
      setTimeout(() => {
        window.location.href = BASE_URL + '/user/bookings.php';
      }, 2000);
    }
  })
  .catch(err => {
    btn.innerHTML = ogText;
    btn.disabled = false;
    if (typeof showToast === 'function') showToast('An error occurred. Please try again.', 'error');
    else alert('An error occurred.');
  });
});
</script>
