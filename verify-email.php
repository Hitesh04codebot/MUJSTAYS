<?php
// verify-email.php
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_once 'includes/helpers.php';
require_once 'includes/mailer.php';

if (empty($_SESSION['user_id'])) redirect(BASE_URL . '/login.php');
if (!empty($_SESSION['is_verified'])) { redirect(BASE_URL . '/'); }

$error = ''; $success = '';
$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $error = 'Invalid request.'; }
    elseif (isset($_POST['resend'])) {
        $user = $pdo->prepare("SELECT * FROM users WHERE id = ?"); $user->execute([$user_id]); $user = $user->fetch();
        if ($user['otp_expires_at'] && strtotime($user['otp_expires_at']) > time() - (int)(OTP_RESEND_COOLDOWN ?? 60)) {
            $error = 'Please wait before requesting a new OTP.';
        } else {
            $otp = generate_otp();
            $pdo->prepare("UPDATE users SET otp_code=?, otp_expires_at=? WHERE id=?")->execute([$otp, date('Y-m-d H:i:s', strtotime('+10 minutes')), $user_id]);
            send_otp_email($user['email'], $user['name'], $otp);
            if (!empty($user['phone'])) send_otp_sms($user['phone'], $otp);
            $success = 'A new OTP has been sent to your email and phone.';
        }
    } else {
        $otp = trim($_POST['otp'] ?? '');
        $user = $pdo->prepare("SELECT * FROM users WHERE id = ?"); $user->execute([$user_id]); $user = $user->fetch();
        if (!$otp) { $error = 'Please enter the OTP.'; }
        elseif ($user['otp_code'] !== $otp) { $error = 'Incorrect OTP. Please check your email and messages.'; }
        elseif (strtotime($user['otp_expires_at']) < time()) { $error = 'OTP has expired. Please request a new one.'; }
        else {
            $pdo->prepare("UPDATE users SET is_verified=1, otp_code=NULL, otp_expires_at=NULL WHERE id=?")->execute([$user_id]);
            $_SESSION['is_verified'] = 1;
            flash_set('success', 'Email verified! Welcome to MUJSTAYS 🎉');
            $dash = match($_SESSION['role']) { 'student'=>BASE_URL.'/user/dashboard.php', 'owner'=>BASE_URL.'/owner/dashboard.php', default=>BASE_URL.'/' };
            redirect($dash);
        }
    }
}
$user_email = $_SESSION['email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Verify Email — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--bg)">
<div style="max-width:460px;width:100%;padding:24px">
  <div class="card">
    <div style="background:linear-gradient(135deg,var(--primary),var(--accent));padding:32px;text-align:center">
      <div style="font-size:48px;margin-bottom:12px">📧</div>
      <h2 style="color:#fff;margin-bottom:8px">Verify Your Account</h2>
      <p style="color:rgba(255,255,255,.8)">We sent a 6-digit code to your email and phone number</p>
    </div>
    <div class="card-body">
      <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
      <form method="POST">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label" for="otp">Enter OTP Code <span class="req">*</span></label>
          <input type="text" id="otp" name="otp" class="form-control"
                 placeholder="6-digit code" maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                 style="text-align:center;font-size:28px;letter-spacing:10px;font-weight:700" autofocus required>
          <div class="form-text" style="text-align:center">Valid for <?= OTP_EXPIRY_MINUTES ?> minutes from when it was sent</div>
        </div>
        <button type="submit" class="btn btn-primary btn-xl btn-w100">
          <i class="fas fa-check-circle"></i> Verify Email
        </button>
      </form>
      <form method="POST" style="margin-top:16px;text-align:center">
        <?= csrf_field() ?>
        <button type="submit" name="resend" class="btn btn-ghost" style="color:var(--accent)">
          <i class="fas fa-redo"></i> Resend OTP
        </button>
      </form>
      <p style="text-align:center;font-size:13px;color:var(--text-muted);margin-top:16px">
        Wrong email? <a href="<?= BASE_URL ?>/logout.php">Log out</a> and register again.
      </p>
    </div>
  </div>
</div>
</body></html>
