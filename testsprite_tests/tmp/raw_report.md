
# TestSprite AI Testing Report(MCP)

---

## 1️⃣ Document Metadata
- **Project Name:** MUJSTAYS
- **Date:** 2026-04-04
- **Prepared by:** TestSprite AI Team

---

## 2️⃣ Requirement Validation Summary

#### Test TC001 postsignupphpcreateanewaccount
- **Test Code:** [TC001_postsignupphpcreateanewaccount.py](./TC001_postsignupphpcreateanewaccount.py)
- **Test Error:** Traceback (most recent call last):
  File "/var/task/handler.py", line 258, in run_with_retry
    exec(code, exec_env)
  File "<string>", line 48, in <module>
  File "<string>", line 21, in test_post_signupphp_create_new_account
AssertionError: Expected 201 Created but got 200

- **Test Visualization and Result:** https://www.testsprite.com/dashboard/mcp/tests/0c53fda4-4117-4951-9dd3-c18f8f8c248a/7ee8b0ff-ca68-457d-b1da-e0d96dbe7699
- **Status:** ❌ Failed
- **Analysis / Findings:** {{TODO:AI_ANALYSIS}}.
---

#### Test TC002 postloginphploginuser
- **Test Code:** [TC002_postloginphploginuser.py](./TC002_postloginphploginuser.py)
- **Test Visualization and Result:** https://www.testsprite.com/dashboard/mcp/tests/0c53fda4-4117-4951-9dd3-c18f8f8c248a/8f521a3a-96c5-4762-b757-b65ec9d41b50
- **Status:** ✅ Passed
- **Analysis / Findings:** {{TODO:AI_ANALYSIS}}.
---

#### Test TC003 postverifyemailphpverifyemailusingotp
- **Test Code:** [TC003_postverifyemailphpverifyemailusingotp.py](./TC003_postverifyemailphpverifyemailusingotp.py)
- **Test Error:** Traceback (most recent call last):
  File "/var/task/handler.py", line 258, in run_with_retry
    exec(code, exec_env)
  File "<string>", line 44, in <module>
  File "<string>", line 22, in test_post_verify_email_with_valid_otp_and_session
AssertionError: Signup failed with status 200

- **Test Visualization and Result:** https://www.testsprite.com/dashboard/mcp/tests/0c53fda4-4117-4951-9dd3-c18f8f8c248a/fd2afe81-7e87-406e-8e73-390c25ac3cf8
- **Status:** ❌ Failed
- **Analysis / Findings:** {{TODO:AI_ANALYSIS}}.
---

#### Test TC004 getindexphphomepagewithfeaturedpgs
- **Test Code:** [TC004_getindexphphomepagewithfeaturedpgs.py](./TC004_getindexphphomepagewithfeaturedpgs.py)
- **Test Visualization and Result:** https://www.testsprite.com/dashboard/mcp/tests/0c53fda4-4117-4951-9dd3-c18f8f8c248a/7a5233e0-f329-4d48-b3e8-d9c60a17669c
- **Status:** ✅ Passed
- **Analysis / Findings:** {{TODO:AI_ANALYSIS}}.
---

#### Test TC005 getexplorephpsearchandfilterpgs
- **Test Code:** [TC005_getexplorephpsearchandfilterpgs.py](./TC005_getexplorephpsearchandfilterpgs.py)
- **Test Error:** Traceback (most recent call last):
  File "/var/task/handler.py", line 258, in run_with_retry
    exec(code, exec_env)
  File "<string>", line 53, in <module>
  File "<string>", line 16, in test_get_explore_php_search_and_filter_pgs
AssertionError: Expected JSON response but got Content-Type: text/html; charset=UTF-8

- **Test Visualization and Result:** https://www.testsprite.com/dashboard/mcp/tests/0c53fda4-4117-4951-9dd3-c18f8f8c248a/ea5d95cc-9752-4c9f-b034-da60bf0c1068
- **Status:** ❌ Failed
- **Analysis / Findings:** {{TODO:AI_ANALYSIS}}.
---

#### Test TC006 getpgdetailphpviewpgdetails
- **Test Code:** [TC006_getpgdetailphpviewpgdetails.py](./TC006_getpgdetailphpviewpgdetails.py)
- **Test Visualization and Result:** https://www.testsprite.com/dashboard/mcp/tests/0c53fda4-4117-4951-9dd3-c18f8f8c248a/e65cb27d-3dd4-403f-ad88-d1d480863bf7
- **Status:** ✅ Passed
- **Analysis / Findings:** {{TODO:AI_ANALYSIS}}.
---

#### Test TC007 postuserbookingsphpmanagebookings
- **Test Code:** [TC007_postuserbookingsphpmanagebookings.py](./TC007_postuserbookingsphpmanagebookings.py)
- **Test Error:** Traceback (most recent call last):
  File "/var/task/handler.py", line 258, in run_with_retry
    exec(code, exec_env)
  File "<string>", line 58, in <module>
  File "<string>", line 18, in test_post_user_bookings_php_manage_bookings
AssertionError: Signup failed: 200 <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up — MUJSTAYS</title>
  <link rel="stylesheet" href="http://localhost/MUJSTAYS/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-left">
    <div class="auth-left-content">
      <div style="font-size:60px;margin-bottom:20px">🎓</div>
      <h2>Join MUJSTAYS</h2>
      <p>Create your free account and start discovering verified PGs near Manipal University Jaipur today.</p>
      <div style="margin-top:40px;display:flex;flex-direction:column;gap:16px">
        <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.85)">
          <i class="fas fa-shield-alt" style="color:#7EB8D3;font-size:18px"></i>
          <span>100% verified PG listings only</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.85)">
          <i class="fas fa-bolt" style="color:#7EB8D3;font-size:18px"></i>
          <span>Instant booking with online payment</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.85)">
          <i class="fas fa-comments" style="color:#7EB8D3;font-size:18px"></i>
          <span>Chat directly with PG owners</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.85)">
          <i class="fas fa-star" style="color:#7EB8D3;font-size:18px"></i>
          <span>Real reviews by verified MUJ students</span>
        </div>
      </div>
    </div>
  </div>

  <div class="auth-right" style="overflow-y:auto">
    <div class="auth-box">
      <a href="http://localhost/MUJSTAYS" style="font-size:22px;font-weight:800;color:var(--primary);font-family:var(--font-head);display:block;margin-bottom:28px">🏠 MUJ<span style="color:var(--accent)">STAYS</span></a>
      <h1>Create Your Account</h1>
      <p class="auth-subtitle">Already have an account? <a href="http://localhost/MUJSTAYS/login.php">Sign in →</a></p>

              <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Please fill in all required fields.</div>
      
      <!-- Role Selection -->
      <p style="font-size:14px;font-weight:600;color:var(--primary);margin-bottom:12px">I am a:</p>
      <div class="role-selection" style="margin-bottom:24px;display:flex;gap:12px">
        <div class="role-card selected" data-role="student" style="flex:1;cursor:pointer;padding:16px;border:2px solid var(--border);border-radius:12px;text-align:center;transition:all 0.2s">
          <div class="role-icon" style="font-size:32px;margin-bottom:8px">🎓</div>
          <h3 style="font-size:16px;margin:0">Student</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:4px 0 0">Looking for a PG</p>
        </div>
        <div class="role-card " data-role="owner" style="flex:1;cursor:pointer;padding:16px;border:2px solid var(--border);border-radius:12px;text-align:center;transition:all 0.2s">
          <div class="role-icon" style="font-size:32px;margin-bottom:8px">🏘️</div>
          <h3 style="font-size:16px;margin:0">PG Owner</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:4px 0 0">List my property</p>
        </div>
      </div>

      <form method="POST" id="signup-form" novalidate>
        <input type="hidden" name="csrf_token" value="556beac6eba3442fd9ca404b627631bca9b590318a80e6238340c555d657248a">        <input type="hidden" id="role-input" name="role" value="student">

        <!-- Basic Info -->
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label" for="name">Full Name <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-user input-icon"></i>
              <input type="text" id="name" name="name" class="form-control"
                     placeholder="Your full name" value="Test User Booking" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="phone">Phone Number</label>
            <div class="input-group">
              <i class="fas fa-phone input-icon"></i>
              <input type="tel" id="phone" name="phone" class="form-control"
                     placeholder="+91 98765 43210" value="">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-email">Email Address <span class="req">*</span></label>
          <div class="input-group">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" id="reg-email" name="email" class="form-control"
                   placeholder="you@jaipur.manipal.edu"
                   value="testuserbooking_1775310627@example.com" required>
          </div>
          <div class="form-text">Preferably your MUJ email (@jaipur.manipal.edu)</div>
        </div>

        <!-- Owner Specific Fields (Hidden by default) -->
        <div id="owner-fields" style="display: none; background: var(--bg2); padding: 20px; border-radius: 12px; margin-bottom: 24px; border: 1px dashed var(--accent)">
          <h3 style="font-size: 15px; margin-bottom: 16px; color: var(--accent)"><i class="fas fa-briefcase"></i> Business Details</h3>
          <div class="form-group">
            <label class="form-label" for="business_name">Business Name / PG Name <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-building input-icon"></i>
              <input type="text" id="business_name" name="business_name" class="form-control" placeholder="e.g. Royal Heritage PG" value="">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="property_address">Primary Property Address <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-map-marked-alt input-icon"></i>
              <input type="text" id="property_address" name="property_address" class="form-control" placeholder="e.g. Plot 12, Behind MUJ Main Gate" value="">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="gender">Gender</label>
          <select id="gender" name="gender" class="form-select">
            <option value="">Prefer not to say</option>
            <option value="male"   >Male</option>
            <option value="female" >Female</option>
            <option value="other"  >Other</option>
          </select>
        </div>

        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label" for="reg-password">Password <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" id="reg-password" name="password" class="form-control" placeholder="Min. 8 chars" required>
              <button type="button" class="toggle-password input-icon-right" data-target="reg-password" style="background:none;border:none;cursor:pointer;color:var(--text-muted)"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="confirm-password">Confirm Password <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" id="confirm-password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="checkbox-item">
            <input type="checkbox" name="agree_terms" required>
            <span style="font-size:13px">I agree to the <a href="http://localhost/MUJSTAYS/terms.php" target="_blank">Terms & Conditions</a></span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-xl btn-w100">
          <i class="fas fa-user-plus"></i> Create Account
        </button>
      </form>
    </div>
  </div>
</div>

<script>
var BASE_URL = 'http://localhost/MUJSTAYS';
// Sync role selection
document.querySelectorAll('.role-card[data-role]').forEach(card => {
  card.addEventListener('click', function() {
    const role = this.dataset.role;
    document.querySelectorAll('.role-card').forEach(c => {
        c.classList.remove('selected');
        c.style.borderColor = 'var(--border)';
    });
    this.classList.add('selected');
    this.style.borderColor = 'var(--primary)';
    document.getElementById('role-input').value = role;
    
    // Toggle owner fields
    const ownerFields = document.getElementById('owner-fields');
    if (role === 'owner') {
        ownerFields.style.display = 'block';
    } else {
        ownerFields.style.display = 'none';
    }
  });
});
// Trigger click on already selected card to ensure fields are shown if needed on reload
document.querySelector('.role-card.selected')?.click();
</script>
<script src="http://localhost/MUJSTAYS/assets/js/main.js"></script>
</body>
</html>


- **Test Visualization and Result:** https://www.testsprite.com/dashboard/mcp/tests/0c53fda4-4117-4951-9dd3-c18f8f8c248a/74e41297-df46-4b25-84ab-fa79a9a27474
- **Status:** ❌ Failed
- **Analysis / Findings:** {{TODO:AI_ANALYSIS}}.
---

#### Test TC008 getuserchatphpmessagingwithowners
- **Test Code:** [TC008_getuserchatphpmessagingwithowners.py](./TC008_getuserchatphpmessagingwithowners.py)
- **Test Error:** Traceback (most recent call last):
  File "/var/task/handler.py", line 258, in run_with_retry
    exec(code, exec_env)
  File "<string>", line 102, in <module>
  File "<string>", line 29, in test_get_user_chat_php_messaging_with_owners
AssertionError: Signup failed: <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up — MUJSTAYS</title>
  <link rel="stylesheet" href="http://localhost/MUJSTAYS/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-left">
    <div class="auth-left-content">
      <div style="font-size:60px;margin-bottom:20px">🎓</div>
      <h2>Join MUJSTAYS</h2>
      <p>Create your free account and start discovering verified PGs near Manipal University Jaipur today.</p>
      <div style="margin-top:40px;display:flex;flex-direction:column;gap:16px">
        <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.85)">
          <i class="fas fa-shield-alt" style="color:#7EB8D3;font-size:18px"></i>
          <span>100% verified PG listings only</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.85)">
          <i class="fas fa-bolt" style="color:#7EB8D3;font-size:18px"></i>
          <span>Instant booking with online payment</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.85)">
          <i class="fas fa-comments" style="color:#7EB8D3;font-size:18px"></i>
          <span>Chat directly with PG owners</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.85)">
          <i class="fas fa-star" style="color:#7EB8D3;font-size:18px"></i>
          <span>Real reviews by verified MUJ students</span>
        </div>
      </div>
    </div>
  </div>

  <div class="auth-right" style="overflow-y:auto">
    <div class="auth-box">
      <a href="http://localhost/MUJSTAYS" style="font-size:22px;font-weight:800;color:var(--primary);font-family:var(--font-head);display:block;margin-bottom:28px">🏠 MUJ<span style="color:var(--accent)">STAYS</span></a>
      <h1>Create Your Account</h1>
      <p class="auth-subtitle">Already have an account? <a href="http://localhost/MUJSTAYS/login.php">Sign in →</a></p>

              <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> An account with this email already exists. <a href="http://localhost/MUJSTAYS/login.php">Log in instead.</a></div>
      
      <!-- Role Selection -->
      <p style="font-size:14px;font-weight:600;color:var(--primary);margin-bottom:12px">I am a:</p>
      <div class="role-selection" style="margin-bottom:24px;display:flex;gap:12px">
        <div class="role-card selected" data-role="student" style="flex:1;cursor:pointer;padding:16px;border:2px solid var(--border);border-radius:12px;text-align:center;transition:all 0.2s">
          <div class="role-icon" style="font-size:32px;margin-bottom:8px">🎓</div>
          <h3 style="font-size:16px;margin:0">Student</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:4px 0 0">Looking for a PG</p>
        </div>
        <div class="role-card " data-role="owner" style="flex:1;cursor:pointer;padding:16px;border:2px solid var(--border);border-radius:12px;text-align:center;transition:all 0.2s">
          <div class="role-icon" style="font-size:32px;margin-bottom:8px">🏘️</div>
          <h3 style="font-size:16px;margin:0">PG Owner</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:4px 0 0">List my property</p>
        </div>
      </div>

      <form method="POST" id="signup-form" novalidate>
        <input type="hidden" name="csrf_token" value="ce380baa3a5043f19bed7f29d8d50c798d9d80177065abf9cfa9135590237ccc">        <input type="hidden" id="role-input" name="role" value="student">

        <!-- Basic Info -->
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label" for="name">Full Name <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-user input-icon"></i>
              <input type="text" id="name" name="name" class="form-control"
                     placeholder="Your full name" value="Test Student" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="phone">Phone Number</label>
            <div class="input-group">
              <i class="fas fa-phone input-icon"></i>
              <input type="tel" id="phone" name="phone" class="form-control"
                     placeholder="+91 98765 43210" value="">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-email">Email Address <span class="req">*</span></label>
          <div class="input-group">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" id="reg-email" name="email" class="form-control"
                   placeholder="you@jaipur.manipal.edu"
                   value="teststudent_tc008@example.com" required>
          </div>
          <div class="form-text">Preferably your MUJ email (@jaipur.manipal.edu)</div>
        </div>

        <!-- Owner Specific Fields (Hidden by default) -->
        <div id="owner-fields" style="display: none; background: var(--bg2); padding: 20px; border-radius: 12px; margin-bottom: 24px; border: 1px dashed var(--accent)">
          <h3 style="font-size: 15px; margin-bottom: 16px; color: var(--accent)"><i class="fas fa-briefcase"></i> Business Details</h3>
          <div class="form-group">
            <label class="form-label" for="business_name">Business Name / PG Name <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-building input-icon"></i>
              <input type="text" id="business_name" name="business_name" class="form-control" placeholder="e.g. Royal Heritage PG" value="">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="property_address">Primary Property Address <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-map-marked-alt input-icon"></i>
              <input type="text" id="property_address" name="property_address" class="form-control" placeholder="e.g. Plot 12, Behind MUJ Main Gate" value="">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="gender">Gender</label>
          <select id="gender" name="gender" class="form-select">
            <option value="">Prefer not to say</option>
            <option value="male"   >Male</option>
            <option value="female" >Female</option>
            <option value="other"  >Other</option>
          </select>
        </div>

        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label" for="reg-password">Password <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" id="reg-password" name="password" class="form-control" placeholder="Min. 8 chars" required>
              <button type="button" class="toggle-password input-icon-right" data-target="reg-password" style="background:none;border:none;cursor:pointer;color:var(--text-muted)"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="confirm-password">Confirm Password <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-lock input-icon"></i>
              <input type="password" id="confirm-password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="checkbox-item">
            <input type="checkbox" name="agree_terms" required>
            <span style="font-size:13px">I agree to the <a href="http://localhost/MUJSTAYS/terms.php" target="_blank">Terms & Conditions</a></span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-xl btn-w100">
          <i class="fas fa-user-plus"></i> Create Account
        </button>
      </form>
    </div>
  </div>
</div>

<script>
var BASE_URL = 'http://localhost/MUJSTAYS';
// Sync role selection
document.querySelectorAll('.role-card[data-role]').forEach(card => {
  card.addEventListener('click', function() {
    const role = this.dataset.role;
    document.querySelectorAll('.role-card').forEach(c => {
        c.classList.remove('selected');
        c.style.borderColor = 'var(--border)';
    });
    this.classList.add('selected');
    this.style.borderColor = 'var(--primary)';
    document.getElementById('role-input').value = role;
    
    // Toggle owner fields
    const ownerFields = document.getElementById('owner-fields');
    if (role === 'owner') {
        ownerFields.style.display = 'block';
    } else {
        ownerFields.style.display = 'none';
    }
  });
});
// Trigger click on already selected card to ensure fields are shown if needed on reload
document.querySelector('.role-card.selected')?.click();
</script>
<script src="http://localhost/MUJSTAYS/assets/js/main.js"></script>
</body>
</html>


- **Test Visualization and Result:** https://www.testsprite.com/dashboard/mcp/tests/0c53fda4-4117-4951-9dd3-c18f8f8c248a/d23297e2-e522-4fcd-8e44-ff9caf1be51b
- **Status:** ❌ Failed
- **Analysis / Findings:** {{TODO:AI_ANALYSIS}}.
---

#### Test TC009 postowneraddlistingphpaddnewpglisting
- **Test Code:** [TC009_postowneraddlistingphpaddnewpglisting.py](./TC009_postowneraddlistingphpaddnewpglisting.py)
- **Test Error:** Traceback (most recent call last):
  File "/var/task/handler.py", line 258, in run_with_retry
    exec(code, exec_env)
  File "<string>", line 107, in <module>
  File "<string>", line 86, in test_post_owner_add_listing
AssertionError: Failed to add listing: <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — MUJSTAYS</title>
  <link rel="stylesheet" href="http://localhost/MUJSTAYS/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
  <!-- Left decorative panel -->
  <div class="auth-left">
    <div class="auth-left-content">
      <div style="font-size:60px;margin-bottom:20px">🏠</div>
      <h2>Welcome Back!</h2>
      <p>Log in to access your personalized PG recommendations, track bookings, and chat with owners.</p>
      <div style="margin-top:40px;display:flex;flex-direction:column;gap:14px">
        <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.85)"><i class="fas fa-check-circle" style="color:#7EB8D3"></i> 500+ Verified PGs Near MUJ</div>
        <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.85)"><i class="fas fa-check-circle" style="color:#7EB8D3"></i> Book Online in Under 2 Minutes</div>
        <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.85)"><i class="fas fa-check-circle" style="color:#7EB8D3"></i> Chat Directly with Owners</div>
      </div>
    </div>
  </div>

  <!-- Right form panel -->
  <div class="auth-right">
    <div class="auth-box">
      <a href="http://localhost/MUJSTAYS" style="font-size:22px;font-weight:800;color:var(--primary);font-family:var(--font-head);display:block;margin-bottom:32px">🏠 MUJ<span style="color:var(--accent)">STAYS</span></a>
      <h1>Sign In</h1>
      <p class="auth-subtitle">New to MUJSTAYS? <a href="http://localhost/MUJSTAYS/signup.php">Create a free account →</a></p>

            
      <form method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="0a46136e91da827cca013ac90694a3941fc10fc3cc9f8fa7c65c667a758a17b7">
        <div class="form-group">
          <label class="form-label" for="email">Email Address <span class="req">*</span></label>
          <div class="input-group">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" id="email" name="email" class="form-control"
                   placeholder="you@example.com"
                   value="" required autofocus>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">
            Password <span class="req">*</span>
            <a href="http://localhost/MUJSTAYS/forgot-password.php" style="float:right;font-weight:400;font-size:13px;color:var(--accent)">Forgot password?</a>
          </label>
          <div class="input-group">
            <i class="fas fa-lock input-icon"></i>
            <input type="password" id="password" name="password" class="form-control"
                   placeholder="••••••••" required>
            <button type="button" class="toggle-password input-icon-right" data-target="password" style="background:none;border:none;cursor:pointer;color:var(--text-muted)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
          <label class="checkbox-item">
            <input type="checkbox" name="remember_me" value="1">
            <span style="font-size:14px">Stay logged in for 24 hours</span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-xl btn-w100">
          <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
      </form>

      <div style="margin-top:32px;padding:20px;background:var(--bg2);border-radius:var(--radius);font-size:13px;color:var(--text-muted)">
        <strong style="color:var(--primary)">Demo Accounts:</strong><br>
        Student: student@mujstays.com · Password: Student@1234<br>
        Owner: owner@mujstays.com · Password: Owner@1234<br>
        Admin: admin@mujstays.com · Password: Admin@1234
      </div>

      <p style="text-align:center;margin-top:24px;font-size:14px;color:var(--text-muted)">
        Don't have an account? <a href="http://localhost/MUJSTAYS/signup.php" style="font-weight:700">Sign up free →</a>
      </p>
    </div>
  </div>
</div>
<script src="http://localhost/MUJSTAYS/assets/js/main.js"></script>
</body>
</html>


- **Test Visualization and Result:** https://www.testsprite.com/dashboard/mcp/tests/0c53fda4-4117-4951-9dd3-c18f8f8c248a/0948227d-7f1f-48f0-abbe-5487a7ee37a1
- **Status:** ❌ Failed
- **Analysis / Findings:** {{TODO:AI_ANALYSIS}}.
---

#### Test TC010 postadminlistingsphpapproveorrejectlistings
- **Test Code:** [TC010_postadminlistingsphpapproveorrejectlistings.py](./TC010_postadminlistingsphpapproveorrejectlistings.py)
- **Test Error:** Traceback (most recent call last):
  File "/var/task/handler.py", line 258, in run_with_retry
    exec(code, exec_env)
  File "<string>", line 15
    match = re.search(r'name=["\']csrf_token["\']\s+value=["\']([^"\']+)["\']', html)
                                  ^
SyntaxError: closing parenthesis ']' does not match opening parenthesis '('

- **Test Visualization and Result:** https://www.testsprite.com/dashboard/mcp/tests/0c53fda4-4117-4951-9dd3-c18f8f8c248a/f7669b66-03c1-41f6-959e-89a171944032
- **Status:** ❌ Failed
- **Analysis / Findings:** {{TODO:AI_ANALYSIS}}.
---


## 3️⃣ Coverage & Matching Metrics

- **30.00** of tests passed

| Requirement        | Total Tests | ✅ Passed | ❌ Failed  |
|--------------------|-------------|-----------|------------|
| ...                | ...         | ...       | ...        |
---


## 4️⃣ Key Gaps / Risks
{AI_GNERATED_KET_GAPS_AND_RISKS}
---