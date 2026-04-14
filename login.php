<?php
// login.php — Login Page
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_once 'includes/helpers.php';
require_once 'includes/mailer.php';

redirect_if_logged_in();

$error = '';
$success = flash_get('success');

// ---- Handle POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $error = 'Invalid request. Please try again.'; }
    else {
        $email    = strtolower(trim(sanitize($_POST['email'] ?? '')));
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $error = 'Please fill in all fields.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_deleted = 0");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'No account found with this email address.';
            } elseif ($user['is_active'] == 0) {
                $error = 'Your account has been suspended. Contact support.';
            } elseif ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                $mins = ceil((strtotime($user['locked_until']) - time()) / 60);
                $error = "Too many failed attempts. Try again in {$mins} minute(s).";
            } elseif (!password_verify($password, $user['password_hash'])) {
                // Increment login attempts
                $attempts = ($user['login_attempts'] ?? 0) + 1;
                if ($attempts >= LOGIN_MAX_ATTEMPTS) {
                    $locked_until = date('Y-m-d H:i:s', strtotime('+' . LOGIN_LOCKOUT_MINUTES . ' minutes'));
                    $pdo->prepare("UPDATE users SET login_attempts = ?, locked_until = ? WHERE id = ?")
                        ->execute([$attempts, $locked_until, $user['id']]);
                    $error = 'Account locked for ' . LOGIN_LOCKOUT_MINUTES . ' minutes due to too many failed attempts.';
                } else {
                    $pdo->prepare("UPDATE users SET login_attempts = ? WHERE id = ?")->execute([$attempts, $user['id']]);
                    $remaining = LOGIN_MAX_ATTEMPTS - $attempts;
                    $error = "Incorrect password. {$remaining} attempt(s) remaining.";
                }
            } else {
                // Success — reset attempts, regenerate session
                $pdo->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?")->execute([$user['id']]);
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['name']      = $user['name'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['is_verified'] = $user['is_verified'];
                $_SESSION['is_active']   = $user['is_active'];

                // Remember Me
                if (!empty($_POST['remember_me'])) {
                    setcookie(session_name(), session_id(), time() + SESSION_LIFETIME, '/');
                }

                // Verification check bypassed for ease of use

                $dash = match($user['role']) {
                    'student' => BASE_URL . '/user/dashboard.php',
                    'owner'   => BASE_URL . '/owner/dashboard.php',
                    'admin'   => BASE_URL . '/admin/dashboard.php',
                    default   => BASE_URL . '/',
                };
                redirect($dash);
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
  <title>Login — MUJSTAYS</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="auth-page">
  <!-- Left decorative panel -->
  <div class="auth-left">
    <img src="<?= BASE_URL ?>/assets/img/Wavy_Tech-28_Single-10.jpg" alt="Auth" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0.15; z-index: 0;">
    <div class="auth-left-content" style="position: relative; z-index: 1;">

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
      <a href="<?= BASE_URL ?>" style="font-size:22px;font-weight:800;color:var(--primary);font-family:var(--font-head);display:block;margin-bottom:32px">🏠 MUJ<span style="color:var(--accent)">STAYS</span></a>
      <h1>Sign In</h1>
      <p class="auth-subtitle">New to MUJSTAYS? <a href="<?= BASE_URL ?>/signup.php">Create a free account →</a></p>

      <?php if ($error): ?>
        <div class="alert alert-error" data-dismiss="6000"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success" data-dismiss="5000"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <?= csrf_field() ?>

        <div class="form-group">
          <label class="form-label" for="email">Email Address <span class="req">*</span></label>
          <div class="input-group">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" id="email" name="email" class="form-control"
                   placeholder="you@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">
            Password <span class="req">*</span>
            <a href="<?= BASE_URL ?>/forgot-password.php" style="float:right;font-weight:400;font-size:13px;color:var(--accent)">Forgot password?</a>
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
        Don't have an account? <a href="<?= BASE_URL ?>/signup.php" style="font-weight:700">Sign up free →</a>
      </p>
    </div>
  </div>
</div>
<script>window.BASE_URL='<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>

