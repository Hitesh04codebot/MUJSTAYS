/* ================================================================
   MUJSTAYS — Global JavaScript
================================================================ */

// ---- Hamburger / Mobile Nav ----
(function () {
  const ham = document.querySelector('.hamburger');
  const nav = document.querySelector('.mobile-nav');
  if (ham && nav) {
    ham.addEventListener('click', () => {
      ham.classList.toggle('open');
      nav.classList.toggle('open');
    });
    nav.querySelectorAll('.nav-link').forEach(l => l.addEventListener('click', () => {
      ham.classList.remove('open');
      nav.classList.remove('open');
    }));
  }
})();

// ---- Accordion ----
document.querySelectorAll('.accordion-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const body = btn.nextElementSibling;
    const isOpen = btn.classList.contains('open');
    // Close all
    document.querySelectorAll('.accordion-btn.open').forEach(b => {
      b.classList.remove('open');
      b.nextElementSibling.classList.remove('open');
    });
    if (!isOpen) {
      btn.classList.add('open');
      body.classList.add('open');
    }
  });
});

// ---- Flash messages auto-dismiss ----
document.querySelectorAll('.alert[data-dismiss]').forEach(el => {
  setTimeout(() => el.style.display = 'none', parseInt(el.dataset.dismiss) || 5000);
});

// ---- Modal helpers ----
function openModal(id) {
  const m = document.getElementById(id);
  if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
  const m = document.getElementById(id);
  if (m) { m.classList.remove('open'); document.body.style.overflow = ''; }
}
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
});
document.querySelectorAll('.modal-close').forEach(btn => {
  btn.addEventListener('click', () => {
    btn.closest('.modal-overlay').classList.remove('open');
    document.body.style.overflow = '';
  });
});

// ---- Notification Bell toggle ----
(function () {
  const bell = document.getElementById('bell-btn');
  const dropdown = document.getElementById('notif-dropdown');
  if (bell && dropdown) {
    bell.addEventListener('click', e => {
      e.stopPropagation();
      dropdown.classList.toggle('open');
    });
    document.addEventListener('click', () => dropdown.classList.remove('open'));
    dropdown.addEventListener('click', e => e.stopPropagation());
  }
})();

// ---- Profile dropdown toggle ----
(function () {
  const profileBtn = document.getElementById('profile-btn');
  const profileMenu = document.getElementById('profile-menu');
  if (profileBtn && profileMenu) {
    profileBtn.addEventListener('click', e => {
      e.stopPropagation();
      profileMenu.classList.toggle('open');
    });
    document.addEventListener('click', () => profileMenu.classList.remove('open'));
  }
})();

// ---- Save / Bookmark PG (AJAX) ----
document.querySelectorAll('.save-btn[data-pg]').forEach(btn => {
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    const pgId = this.dataset.pg;
    fetch(BASE_URL + '/api/save-pg.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ pg_id: pgId, csrf: CSRF_TOKEN }),
    })
      .then(r => r.json())
      .then(data => {
        if (data.saved) {
          this.classList.add('saved');
          this.title = 'Remove from saved';
          this.innerHTML = '♥';
        } else {
          this.classList.remove('saved');
          this.title = 'Save this PG';
          this.innerHTML = '♡';
        }
        showToast(data.message, 'success');
      })
      .catch(() => showToast('Please log in to save PGs', 'warning'));
  });
});

// ---- Compare PG ----
let compareList = JSON.parse(sessionStorage.getItem('compareList') || '[]');
document.querySelectorAll('.compare-btn[data-pg]').forEach(btn => {
  const pgId = btn.dataset.pg;
  if (compareList.includes(pgId)) btn.classList.add('added');

  btn.addEventListener('click', function () {
    if (compareList.includes(pgId)) {
      compareList = compareList.filter(id => id !== pgId);
      this.classList.remove('added');
      this.textContent = '+ Compare';
    } else {
      if (compareList.length >= 3) { showToast('You can compare up to 3 PGs', 'warning'); return; }
      compareList.push(pgId);
      this.classList.add('added');
      this.textContent = '✓ Added';
    }
    sessionStorage.setItem('compareList', JSON.stringify(compareList));
    updateCompareBar();
  });
});

function updateCompareBar() {
  const bar = document.getElementById('compare-bar');
  if (!bar) return;
  if (compareList.length === 0) { bar.style.display = 'none'; return; }
  bar.style.display = 'flex';
  bar.querySelector('#compare-count').textContent = compareList.length;
  bar.querySelector('#compare-link').href = BASE_URL + '/user/compare.php?ids=' + compareList.join(',');
}
updateCompareBar();

// ---- Gallery Lightbox ----
(function () {
  const gallery = document.querySelectorAll('.gallery-thumb');
  const main = document.querySelector('.gallery-main img');
  if (gallery.length && main) {
    gallery.forEach((thumb, i) => {
      thumb.addEventListener('click', () => {
        gallery.forEach(t => t.classList.remove('active'));
        thumb.classList.add('active');
        main.src = thumb.querySelector('img').src.replace('_thumb', '');
      });
    });
  }

  // Lightbox
  const lightbox = document.getElementById('lightbox');
  if (lightbox && main) {
    main.addEventListener('click', () => { lightbox.classList.add('open'); lightbox.querySelector('img').src = main.src; });
    document.getElementById('lb-close')?.addEventListener('click', () => lightbox.classList.remove('open'));
    lightbox.addEventListener('click', e => { if (e.target === lightbox) lightbox.classList.remove('open'); });
  }
})();

// ---- Price range slider ----
(function () {
  const minSlider = document.getElementById('price-min');
  const maxSlider = document.getElementById('price-max');
  const minLabel = document.getElementById('price-min-label');
  const maxLabel = document.getElementById('price-max-label');
  if (minSlider && maxSlider) {
    function updateSlider() {
      const min = parseInt(minSlider.value);
      const max = parseInt(maxSlider.value);
      if (min > max) minSlider.value = max;
      if (maxLabel) maxLabel.textContent = '₹' + parseInt(maxSlider.value).toLocaleString();
      if (minLabel) minLabel.textContent = '₹' + parseInt(minSlider.value).toLocaleString();
    }
    minSlider.addEventListener('input', updateSlider);
    maxSlider.addEventListener('input', updateSlider);
    updateSlider();
  }
})();

// ---- Animated stat counters ----
function animateCounters() {
  document.querySelectorAll('.stat-counter').forEach(el => {
    const target = parseInt(el.dataset.target || el.textContent);
    const duration = 1800;
    const step = target / (duration / 16);
    let current = 0;
    const timer = setInterval(() => {
      current += step;
      if (current >= target) { current = target; clearInterval(timer); }
      el.textContent = Math.floor(current).toLocaleString() + (el.dataset.suffix || '');
    }, 16);
  });
}

const statsSection = document.querySelector('.stats-section');
if (statsSection) {
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => { if (entry.isIntersecting) { animateCounters(); observer.unobserve(entry.target); } });
  }, { threshold: 0.3 });
  observer.observe(statsSection);
}

// ---- Toast Notifications ----
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container') || createToastContainer();
  const toast = document.createElement('div');
  const icons = { success: '✓', error: '✕', warning: '⚠', info: 'ℹ' };
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<span class="toast-icon">${icons[type] || 'ℹ'}</span> ${message}`;
  container.appendChild(toast);
  requestAnimationFrame(() => toast.classList.add('show'));
  setTimeout(() => {
    toast.classList.remove('show');
    toast.addEventListener('transitionend', () => toast.remove());
  }, 3500);
}

function createToastContainer() {
  const div = document.createElement('div');
  div.id = 'toast-container';
  div.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;';
  document.body.appendChild(div);

  const style = document.createElement('style');
  style.textContent = `.toast{display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:10px;background:#fff;box-shadow:0 4px 20px rgba(0,0,0,.15);font-size:14px;font-weight:500;transform:translateX(120%);transition:transform .3s cubic-bezier(.4,0,.2,1);min-width:220px;max-width:320px;border-left:4px solid #ccc}.toast.show{transform:translateX(0)}.toast-success{border-color:#27AE60;color:#1D8348}.toast-error{border-color:#E74C3C;color:#A93226}.toast-warning{border-color:#F39C12;color:#B7770D}.toast-info{border-color:#3498DB;color:#1F618D}.toast-icon{font-size:16px}`;
  document.head.appendChild(style);
  return div;
}

// ---- Tab switcher ----
document.querySelectorAll('.tab-btn[data-tab]').forEach(btn => {
  btn.addEventListener('click', function () {
    const group = this.closest('[data-tab-group]');
    const targetId = this.dataset.tab;
    if (group) {
      group.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      group.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
    } else {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
    }
    this.classList.add('active');
    const pane = document.getElementById(targetId);
    if (pane) pane.style.display = 'block';
  });
});

// ---- Dynamic booking price calculator ----
(function () {
  const roomSelect = document.querySelectorAll('input[name="room_type_id"]');
  const durationInput = document.getElementById('duration');
  const priceDisplay = document.getElementById('total-price');
  if (!roomSelect.length || !priceDisplay) return;

  function updatePrice() {
    const selected = document.querySelector('input[name="room_type_id"]:checked');
    const duration = parseInt(durationInput?.value || 1);
    if (selected) {
      const price = parseInt(selected.dataset.price || 0);
      const deposit = parseInt(selected.dataset.deposit || 0);
      const total = price * duration + deposit;
      priceDisplay.textContent = '₹' + total.toLocaleString();
      const breakdown = document.getElementById('price-breakdown');
      if (breakdown) {
        breakdown.innerHTML = `Rent: ₹${(price * duration).toLocaleString()} + Deposit: ₹${deposit.toLocaleString()}`;
      }
    }
  }

  roomSelect.forEach(r => r.addEventListener('change', updatePrice));
  durationInput?.addEventListener('input', updatePrice);
  updatePrice();
})();

// ---- Smooth scroll for anchor links ----
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});

// ---- Password visibility toggle ----
document.querySelectorAll('.toggle-password').forEach(btn => {
  btn.addEventListener('click', function () {
    const input = document.getElementById(this.dataset.target);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    this.innerHTML = input.type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
  });
});

// ---- Signup role selection ----
document.querySelectorAll('.role-card[data-role]').forEach(card => {
  card.addEventListener('click', function () {
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    this.classList.add('selected');
    const roleInput = document.getElementById('role-input');
    if (roleInput) roleInput.value = this.dataset.role;

    // Show correct fields
    document.querySelectorAll('.role-fields').forEach(f => f.style.display = 'none');
    const fields = document.getElementById(this.dataset.role + '-fields');
    if (fields) fields.style.display = 'block';
  });
});

// ---- Form AJAX submit helper ----
function ajaxPost(url, data, onSuccess, onError) {
  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    body: JSON.stringify(data),
  })
    .then(r => r.json())
    .then(onSuccess)
    .catch(onError || (e => showToast('An error occurred', 'error')));
}

// ---- Multi-step form ----
(function () {
  const steps = document.querySelectorAll('.form-step');
  if (!steps.length) return;
  let current = 0;

  function showStep(n) {
    steps.forEach((s, i) => s.style.display = i === n ? 'block' : 'none');
    document.querySelectorAll('.step-indicator').forEach((ind, i) => {
      ind.classList.toggle('active', i === n);
      ind.classList.toggle('done', i < n);
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  document.querySelectorAll('.next-step').forEach(btn => {
    btn.addEventListener('click', () => {
      if (validateStep(steps[current])) { current++; showStep(current); }
    });
  });

  document.querySelectorAll('.prev-step').forEach(btn => {
    btn.addEventListener('click', () => { current--; showStep(current); });
  });

  function validateStep(step) {
    let valid = true;
    step.querySelectorAll('[required]').forEach(input => {
      if (!input.value.trim()) {
        input.classList.add('is-invalid');
        valid = false;
        input.addEventListener('input', () => input.classList.remove('is-invalid'), { once: true });
      }
    });
    return valid;
  }

  showStep(0);
})();

// ---- Globals ----
var BASE_URL = window.BASE_URL || document.documentElement.dataset.baseUrl || '';
var CSRF_TOKEN = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content || '';
