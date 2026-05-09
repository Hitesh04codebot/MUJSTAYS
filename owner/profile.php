<?php
// owner/profile.php — Owner Profile & KYC Management
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';
require_once '../includes/upload_handler.php';

require_auth('owner');
$uid = current_user_id();
$error = ''; $success = '';

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

// Get current KYC status
$stmt = $pdo->prepare("SELECT * FROM kyc_documents WHERE owner_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$uid]);
$kyc = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid request.';
    } elseif (isset($_POST['update_profile'])) {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        
        if (!$name) {
            $error = 'Name is required.';
        } else {
            $photo_path = $user['profile_photo'];
            if (!empty($_FILES['profile_photo']['name'])) {
                try {
                    $path = upload_file($_FILES['profile_photo'], 'avatars/' . $uid, true);
                    if ($path) $photo_path = $path;
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            if (!$error) {
                $pdo->prepare("UPDATE users SET name = ?, phone = ?, profile_photo = ?, updated_at = NOW() WHERE id = ?")
                    ->execute([$name, $phone, $photo_path, $uid]);
                $_SESSION['name'] = $name;
                $_SESSION['profile_photo'] = $photo_path;
                $success = 'Profile updated successfully!';
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$uid]);
                $user = $stmt->fetch();
            }
        }
    } elseif (isset($_POST['remove_photo'])) {
        if (!empty($user['profile_photo'])) {
            try { delete_upload($user['profile_photo']); } catch (Exception $e) {}
            $pdo->prepare("UPDATE users SET profile_photo = NULL, updated_at = NOW() WHERE id = ?")->execute([$uid]);
            $_SESSION['profile_photo'] = '';
            $success = 'Profile photo removed successfully!';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            $user = $stmt->fetch();
        }
    } elseif (isset($_POST['upload_kyc'])) {
        $doc_type = sanitize($_POST['doc_type'] ?? 'Aadhaar Card');
        if (empty($_FILES['kyc_file']['name'])) {
            $error = 'Please select a document to upload.';
        } else {
            try {
                $path = upload_file($_FILES['kyc_file'], 'kyc/' . $uid, true);
                if ($path) {
                    $pdo->prepare("INSERT INTO kyc_documents (owner_id, doc_type, file_path, status) VALUES (?, ?, ?, 'pending')")
                        ->execute([$uid, $doc_type, $path]);
                    $success = 'KYC document uploaded successfully and is now under review.';
                    // Refresh KYC data
                    $stmt = $pdo->prepare("SELECT * FROM kyc_documents WHERE owner_id = ? ORDER BY created_at DESC LIMIT 1");
                    $stmt->execute([$uid]);
                    $kyc = $stmt->fetch();
                } else {
                    $error = 'Failed to upload document.';
                }
            } catch (Exception $e) { $error = $e->getMessage(); }
        }
    } elseif (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        if (!password_verify($current, $user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
            $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?")
                ->execute([$hash, $uid]);
            
            // Force user to login with new password
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['flash_success'] = 'Password changed successfully! Please log in with your new password.';
            header('Location: ' . BASE_URL . '/login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Profile & KYC — MUJSTAYS Owner</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once '../components/navbar.php'; ?>
<div class="dashboard-layout">
    <?php require_once '../components/sidebar.php'; ?>
    <div class="main-content">
        <h2 class="mb-24">Profile & Identity Verification</h2>
        
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <div class="row">
            <!-- Profile Info -->
            <div style="flex: 1; min-width: 300px;">
                <div class="card mb-24">
                    <div class="card-header"><h3>Basic Profile</h3></div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="form-group">
                                <label class="form-label">Profile Photo (Optional)</label>
                                <?php if (!empty($user['profile_photo'])): ?>
                                    <div style="display:flex; align-items:center; gap:16px; margin-bottom:12px">
                                        <img src="<?= get_asset_url($user['profile_photo']) ?>" style="width:60px; height:60px; border-radius:50%; object-fit:cover">
                                        <button type="submit" name="remove_photo" value="1" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:var(--danger)" formnovalidate>Remove Photo</button>
                                    </div>
                                <?php endif; ?>
                                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            <button type="submit" name="update_profile" value="1" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mb-24">
                    <div class="card-header"><h3>Security</h3></div>
                    <div class="card-body">
                        <form method="POST">
                            <?= csrf_field() ?>
                            <div class="form-group">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" name="change_password" value="1" class="btn btn-outline">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- KYC Section -->
            <div style="flex: 1; min-width: 300px;">
                <div class="card">
                    <div class="card-header"><h3>KYC Verification</h3></div>
                    <div class="card-body">
                        <?php if ($user['is_kyc_verified']): ?>
                            <div class="alert alert-success"><i class="fas fa-check-circle"></i> Identity Verified</div>
                            <p style="font-size: 14px;">Your account is fully verified. You can now list properties and receive bookings.</p>
                        <?php elseif ($kyc && $kyc['status'] === 'pending'): ?>
                            <div class="alert alert-warning"><i class="fas fa-clock"></i> Verification in Progress</div>
                            <p style="font-size: 14px;">Your document (<?= htmlspecialchars($kyc['doc_type']) ?>) is being reviewed by our team.</p>
                        <?php else: ?>
                            <?php if ($kyc && $kyc['status'] === 'rejected'): ?>
                                <div class="alert alert-error"><i class="fas fa-times-circle"></i> Rejected: <?= htmlspecialchars($kyc['rejection_reason'] ?? 'Document invalid.') ?></div>
                            <?php endif; ?>
                            
                            <p class="mb-16">To list properties on MUJSTAYS, we need to verify your identity.</p>
                            <form method="POST" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <div class="form-group">
                                    <label class="form-label">Document Type</label>
                                    <select name="doc_type" class="form-select" required>
                                        <option value="Aadhaar Card">Aadhaar Card</option>
                                        <option value="PAN Card">PAN Card</option>
                                        <option value="Driving License">Driving License</option>
                                        <option value="Electricity Bill">Electricity Bill (Address Proof)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Select Document (Image/PDF)</label>
                                    <input type="file" name="kyc_file" class="form-control" accept="image/*,application/pdf" required>
                                </div>
                                <button type="submit" name="upload_kyc" value="1" class="btn btn-primary btn-w100">Upload Verification Doc</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../components/footer.php'; ?>
</body>
</html>
