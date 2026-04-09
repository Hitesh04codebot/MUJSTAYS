<?php
// forgot-password.php
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';
require_once 'includes/mailer.php';

$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['email'])) {
    if (!verify_csrf()) { $error='Invalid request.'; }
    else {
        $email = strtolower(trim($_POST['email']));
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND is_deleted=0");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $otp = generate_otp();
            $pdo->prepare("UPDATE users SET otp_code=?,otp_expires_at=? WHERE id=?")
                ->execute([$otp, date('Y-m-d H:i:s', strtotime('+10 minutes')), $user['id']]);
            send_password_reset_email($email, $user['name'], $otp);
        }
        // Always show success (security: don't reveal if email exists)
        $success = 'If an account with that email exists, a reset OTP has been sent.';
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Forgot Password — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--bg)">
<div style="max-width:440px;width:100%;padding:24px">
  <a href="<?= BASE_URL ?>" style="display:block;text-align:center;font-size:22px;font-weight:800;color:var(--primary);font-family:var(--font-head);margin-bottom:24px">🏠 MUJ<span style="color:var(--accent)">STAYS</span></a>
  <div class="card">
    <div class="card-body">
      <h2 style="margin-bottom:8px">Forgot Password?</h2>
      <p style="color:var(--text-muted);margin-bottom:24px">Enter your registered email and we'll send you a reset OTP.</p>
      <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?> <a href="<?= BASE_URL ?>/reset-password.php">Enter OTP →</a></div><?php endif; ?>
      <?php if (!$success): ?>
      <form method="POST">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label" for="email">Registered Email <span class="req">*</span></label>
          <div class="input-group">
            <i class="fas fa-envelope input-icon"></i>
            <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" required autofocus>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-xl btn-w100"><i class="fas fa-paper-plane"></i> Send Reset OTP</button>
      </form>
      <?php endif; ?>
      <p style="text-align:center;margin-top:20px;font-size:14px"><a href="<?= BASE_URL ?>/login.php">← Back to Login</a></p>
    </div>
  </div>
</div>
</body></html>
