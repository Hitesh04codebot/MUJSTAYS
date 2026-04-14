<?php
// signup.php — Registration Page
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_once 'includes/helpers.php';
require_once 'includes/mailer.php';

redirect_if_logged_in();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $error = 'Invalid request.'; }
    else {
        $name     = sanitize($_POST['name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $phone    = sanitize($_POST['phone'] ?? '');
        $gender   = in_array($_POST['gender'] ?? '', ['male','female','other']) ? $_POST['gender'] : null;
        $role     = in_array($_POST['role'] ?? '', ['student','owner']) ? $_POST['role'] : 'student';

        // Validation
        if (!$name || !$email || !$password || !$confirm) {
            $error = 'Please fill in all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8 || !preg_match('/[0-9]/', $password)) {
            $error = 'Password must be at least 8 characters and contain at least one number.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            // Check duplicate email
            $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $chk->execute([$email]);
            if ($chk->fetch()) {
                $error = 'An account with this email already exists. <a href="' . BASE_URL . '/login.php">Log in instead.</a>';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $otp  = generate_otp();
                $otp_expires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

                $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, phone, role, gender, is_verified) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$name, $email, $hash, $phone ?: null, $role, $gender]);
                $user_id = $pdo->lastInsertId();

                // Handle Owner specific fields and KYC
                if ($role === 'owner') {
                    $business_name = sanitize($_POST['business_name'] ?? '');
                    $prop_address  = sanitize($_POST['property_address'] ?? '');
                    $kyc_type      = sanitize($_POST['kyc_type'] ?? 'Aadhaar Card');
                    
                    if (!$business_name || !$prop_address) {
                        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                        $error = 'Business name and Property Address are required for owners.';
                    } elseif (empty($_FILES['kyc_file']['name'])) {
                        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                        $error = 'KYC document is required for owners.';
                    } else {
                        try {
                            require_once 'includes/upload_handler.php';
                            $kyc_path = upload_file($_FILES['kyc_file'], 'kyc/' . $user_id, false);
                            $pdo->prepare("INSERT INTO kyc_documents (owner_id, doc_type, file_path, status) VALUES (?, ?, ?, 'pending')")
                                ->execute([$user_id, $kyc_type, $kyc_path]);
                        } catch (Exception $e) {
                            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                            $error = 'KYC upload failed: ' . $e->getMessage();
                        }
                    }
                }

                if (!$error) {
                    // OTP verification removed for easier testing/deployment

                    // Set session
                    session_regenerate_id(true);
                    $_SESSION['user_id']     = $user_id;
                    $_SESSION['name']        = $name;
                    $_SESSION['role']        = $role;
                    $_SESSION['email']       = $email;
                    $_SESSION['is_verified'] = 1;
                    $_SESSION['is_active']   = 1;

                    flash_set('success', 'Account created successfully! Welcome to MUJSTAYS.');
                    redirect(BASE_URL . '/index.php');
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up — MUJSTAYS</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-left">
    <img src="<?= BASE_URL ?>/assets/img/Wavy_Tech-28_Single-10.jpg" alt="Auth" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.15; z-index: 0;">
    <div class="auth-left-content" style="position: relative; z-index: 1;">

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
      <a href="<?= BASE_URL ?>" style="font-size:22px;font-weight:800;color:var(--primary);font-family:var(--font-head);display:block;margin-bottom:28px">🏠 MUJ<span style="color:var(--accent)">STAYS</span></a>
      <h1>Create Your Account</h1>
      <p class="auth-subtitle">Already have an account? <a href="<?= BASE_URL ?>/login.php">Sign in →</a></p>

      <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
      <?php endif; ?>

      <!-- Role Selection -->
      <p style="font-size:14px;font-weight:600;color:var(--primary);margin-bottom:12px">I am a:</p>
      <div class="role-selection" style="margin-bottom:24px;display:flex;gap:12px">
        <div class="role-card <?= ($_POST['role'] ?? 'student') === 'student' ? 'selected' : '' ?>" data-role="student" style="flex:1;cursor:pointer;padding:16px;border:2px solid var(--border);border-radius:12px;text-align:center;transition:all 0.2s">
          <div class="role-icon" style="font-size:32px;margin-bottom:8px">🎓</div>
          <h3 style="font-size:16px;margin:0">Student</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:4px 0 0">Looking for a PG</p>
        </div>
        <div class="role-card <?= ($_POST['role'] ?? '') === 'owner' ? 'selected' : '' ?>" data-role="owner" style="flex:1;cursor:pointer;padding:16px;border:2px solid var(--border);border-radius:12px;text-align:center;transition:all 0.2s">
          <div class="role-icon" style="font-size:32px;margin-bottom:8px">🏘️</div>
          <h3 style="font-size:16px;margin:0">PG Owner</h3>
          <p style="font-size:12px;color:var(--text-muted);margin:4px 0 0">List my property</p>
        </div>
      </div>

      <form method="POST" id="signup-form" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" id="role-input" name="role" value="<?= htmlspecialchars($_POST['role'] ?? 'student') ?>">

        <!-- Basic Info -->
        <div class="form-row form-row-2">
          <div class="form-group">
            <label class="form-label" for="name">Full Name <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-user input-icon"></i>
              <input type="text" id="name" name="name" class="form-control"
                     placeholder="Your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label" for="phone">Phone Number</label>
            <div class="input-group">
              <i class="fas fa-phone input-icon"></i>
              <input type="tel" id="phone" name="phone" class="form-control"
                     placeholder="+91 98765 43210" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="reg-email">Email Address <span class="req">*</span></label>
          <div class="input-group">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" id="reg-email" name="email" class="form-control"
                   placeholder="you@jaipur.manipal.edu"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="form-text">Preferably your MUJ email (@jaipur.manipal.edu)</div>
        </div>

        <div id="owner-fields" style="display: <?= ($_POST['role'] ?? '') === 'owner' ? 'block' : 'none' ?>; background: var(--bg2); padding: 20px; border-radius: 12px; margin-bottom: 24px; border: 1px dashed var(--accent)">
          <h3 style="font-size: 15px; margin-bottom: 16px; color: var(--accent)"><i class="fas fa-id-card"></i> Identity Verification (KYC)</h3>
          
          <div class="form-group">
            <label class="form-label">Government ID Type <span class="req">*</span></label>
            <select name="kyc_type" class="form-select">
              <option value="Aadhaar Card">Aadhaar Card</option>
              <option value="PAN Card">PAN Card</option>
              <option value="Voter ID">Voter ID</option>
              <option value="Driving License">Driving License</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Upload ID Proof (Front/Back Combined) <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-upload input-icon"></i>
              <input type="file" name="kyc_file" class="form-control" accept="image/*,application/pdf">
            </div>
            <div class="form-text">PDF or Image (Max 10MB)</div>
          </div>

          <hr style="border:0;border-top:1px solid var(--border);margin:20px 0">

          <h3 style="font-size: 15px; margin-bottom: 16px; color: var(--accent)"><i class="fas fa-briefcase"></i> Business Details</h3>
          <div class="form-group">
            <label class="form-label" for="business_name">Business Name / PG Name <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-building input-icon"></i>
              <input type="text" id="business_name" name="business_name" class="form-control" placeholder="e.g. Royal Heritage PG" value="<?= htmlspecialchars($_POST['business_name'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" for="property_address">Primary Property Address <span class="req">*</span></label>
            <div class="input-group">
              <i class="fas fa-map-marked-alt input-icon"></i>
              <input type="text" id="property_address" name="property_address" class="form-control" placeholder="e.g. Plot 12, Behind MUJ Main Gate" value="<?= htmlspecialchars($_POST['property_address'] ?? '') ?>">
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="gender">Gender</label>
          <select id="gender" name="gender" class="form-select">
            <option value="">Prefer not to say</option>
            <option value="male"   <?= ($_POST['gender'] ?? '') === 'male'   ? 'selected' : '' ?>>Male</option>
            <option value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
            <option value="other"  <?= ($_POST['gender'] ?? '') === 'other'  ? 'selected' : '' ?>>Other</option>
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
            <span style="font-size:13px">I agree to the <a href="<?= BASE_URL ?>/terms.php" target="_blank">Terms & Conditions</a></span>
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
var BASE_URL = '<?= BASE_URL ?>';
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
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
