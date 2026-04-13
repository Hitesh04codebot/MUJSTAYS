(function() {
  'use strict';

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
  window.openModal = function(id) {
    const m = document.getElementById(id);
    if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
  };
  window.closeModal = function(id) {
    const m = document.getElementById(id);
    if (m) { m.classList.remove('open'); document.body.style.overflow = ''; }
  };
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
  });
  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
      btn.closest('.modal-overlay').classList.remove('open');
      document.body.style.overflow = '';
    });
  });

  // ---- Dropdown / Toggle Helpers ----
  document.addEventListener('DOMContentLoaded', () => {
    const bell = document.getElementById('bell-btn');
    const notifDropdown = document.getElementById('notif-dropdown');
    if (bell && notifDropdown) {
      bell.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        document.getElementById('profile-menu')?.classList.remove('open');
        notifDropdown.classList.toggle('open');
      });
    }

    const profileBtn = document.getElementById('profile-btn');
    const profileMenu = document.getElementById('profile-menu');
    if (profileBtn && profileMenu) {
      profileBtn.addEventListener('click', e => {
        e.preventDefault(); e.stopPropagation();
        document.getElementById('notif-dropdown')?.classList.remove('open');
        profileMenu.classList.toggle('open');
      });
    }

    document.addEventListener('click', () => {
      document.querySelectorAll('.notif-dropdown, .profile-menu').forEach(d => d.classList.remove('open'));
    });

    document.querySelectorAll('.notif-dropdown, .profile-menu').forEach(d => {
      d.addEventListener('click', e => e.stopPropagation());
    });
  });

  // ---- Save / Bookmark PG (AJAX) ----
  document.querySelectorAll('.save-btn[data-pg]').forEach(btn => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const pgId = this.dataset.pg;
      fetch(window.BASE_URL + '/api/save-pg.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pg_id: pgId, csrf: window.CSRF_TOKEN }),
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
          if (window.showToast) showToast(data.message, 'success');
        })
        .catch(() => { if (window.showToast) showToast('Please log in to save PGs', 'warning'); });
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
        if (compareList.length >= 3) { if (window.showToast) showToast('You can compare up to 3 PGs', 'warning'); return; }
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
    bar.querySelector('#compare-link').href = window.BASE_URL + '/user/compare.php?ids=' + compareList.join(',');
  }
  updateCompareBar();

  // ---- Toast Notifications ----
  window.showToast = function(message, type = 'info') {
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
  };

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

  // ---- Password visibility toggle ----
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function () {
      const input = document.getElementById(this.dataset.target);
      if (!input) return;
      input.type = input.type === 'password' ? 'text' : 'password';
      this.innerHTML = input.type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
    });
  });

})();
