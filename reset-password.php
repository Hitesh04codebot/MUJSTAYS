<?php
// reset-password.php
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!verify_csrf()) { $error='Invalid request.'; }
    else {
        $email  = strtolower(trim($_POST['email']??''));
        $otp    = trim($_POST['otp']??'');
        $pw     = $_POST['password']??'';
        $confirm= $_POST['confirm_password']??'';
        if (!$email||!$otp||!$pw||!$confirm) { $error='All fields required.'; }
        elseif (strlen($pw)<8 || !preg_match('/[0-9]/',$pw)) { $error='Password must be 8+ chars with at least 1 number.'; }
        elseif ($pw!==$confirm) { $error='Passwords do not match.'; }
        else {
            $stmt=$pdo->prepare("SELECT * FROM users WHERE email=? AND otp_code=?"); $stmt->execute([$email,$otp]); $user=$stmt->fetch();
            if (!$user) { $error='Invalid OTP or email address.'; }
            elseif (strtotime($user['otp_expires_at'])<time()) { $error='OTP has expired. Please request a new one.'; }
            else {
                $hash=password_hash($pw,PASSWORD_BCRYPT,['cost'=>12]);
                $pdo->prepare("UPDATE users SET password_hash=?,otp_code=NULL,otp_expires_at=NULL,login_attempts=0,locked_until=NULL WHERE id=?")->execute([$hash,$user['id']]);
                flash_set('success','Password reset successfully! Please log in with your new password.');
                redirect(BASE_URL.'/login.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reset Password — MUJSTAYS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></head>
<body style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--bg)">
<div style="max-width:440px;width:100%;padding:24px">
  <a href="<?= BASE_URL ?>" style="display:block;text-align:center;font-size:22px;font-weight:800;color:var(--primary);font-family:var(--font-head);margin-bottom:24px">🏠 MUJ<span style="color:var(--accent)">STAYS</span></a>
  <div class="card"><div class="card-body">
    <h2 style="margin-bottom:8px">Reset Password</h2>
    <p style="color:var(--text-muted);margin-bottom:24px">Enter the OTP from your email and set a new password.</p>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">Email Address <span class="req">*</span></label>
        <div class="input-group"><i class="fas fa-envelope input-icon"></i>
        <input type="email" name="email" class="form-control" placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email']??'') ?>" required></div>
      </div>
      <div class="form-group">
        <label class="form-label">OTP Code <span class="req">*</span></label>
        <input type="text" name="otp" class="form-control" placeholder="6-digit code" maxlength="6" inputmode="numeric" style="text-align:center;font-size:24px;letter-spacing:8px;font-weight:700" required>
      </div>
      <div class="form-group">
        <label class="form-label">New Password <span class="req">*</span></label>
        <div class="input-group"><i class="fas fa-lock input-icon"></i>
        <input type="password" id="new-pw" name="password" class="form-control" placeholder="Min 8 chars" required>
        <button type="button" class="toggle-password input-icon-right" data-target="new-pw" style="background:none;border:none;cursor:pointer;color:var(--text-muted)"><i class="fas fa-eye"></i></button></div>
      </div>
      <div class="form-group">
        <label class="form-label">Confirm New Password <span class="req">*</span></label>
        <div class="input-group"><i class="fas fa-lock input-icon"></i>
        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required></div>
      </div>
      <button type="submit" class="btn btn-primary btn-xl btn-w100"><i class="fas fa-key"></i> Reset Password</button>
    </form>
    <p style="text-align:center;margin-top:16px;font-size:14px"><a href="<?= BASE_URL ?>/forgot-password.php">← Resend OTP</a></p>
  </div></div>
</div>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body></html>
