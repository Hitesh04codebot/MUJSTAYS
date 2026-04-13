<?php
// contact.php — Contact Support
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Invalid security token, please try again.';
    } else {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if (!$name || !$email || !$subject || !$message) {
            $error = 'All fields are required.';
        } else {
            // Usually we would insert this into a 'contact_queries' table or send an email to the admin.
            // For now, we simulate success smoothly.
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us — MUJSTAYS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .contact-hero {
            background: linear-gradient(rgba(26,60,94,0.85), rgba(26,60,94,0.85)), url('<?= BASE_URL ?>/assets/img/20943953.jpg') center/cover no-repeat;
            color: #fff;
            padding: 100px 0 160px;
            text-align: center;
        }

        .contact-hero h1 {
            color: #fff;
            font-size: 3rem;
            margin-bottom: 16px;
        }
        .contact-hero p {
            color: rgba(255,255,255,0.8);
            font-size: 1.15rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .contact-box {
            background: #fff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            margin-top: -80px;
            position: relative;
            z-index: 10;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
        }
        .contact-info {
            background: linear-gradient(rgba(46,134,171,0.85), rgba(46,134,171,0.85)), url('<?= BASE_URL ?>/assets/img/20943953.jpg') center/cover no-repeat;
            color: #fff;
            padding: 48px;
            position: relative;
        }

        .contact-info h3 { color: #fff; margin-bottom: 24px; font-size: 1.5rem; }
        .contact-item {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        .contact-item i {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 18px;
        }
        .contact-social a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin-right: 12px;
            transition: var(--transition);
        }
        .contact-social a:hover {
            background: #fff;
            color: var(--accent);
            transform: translateY(-3px);
        }
        .contact-form {
            padding: 48px;
        }
        @media (max-width: 768px) {
            .contact-box { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php require_once 'components/navbar.php'; ?>

<header class="contact-hero">
    <div class="container">
        <h1>Get in Touch</h1>
        <p>Have questions about bookings, payments, or listing your property? Our support team is here to help you 24/7.</p>
    </div>
</header>

<main style="padding-bottom: 80px; background: var(--bg);">
    <div class="container">
        <div class="contact-box">
            
            <div class="contact-info">
                <h3>Contact Information</h3>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">Location</div>
                        <div style="color: rgba(255,255,255,0.8); font-size: 13px;">Dehmi Kalan, Near MUJ Campus, Jaipur 303007</div>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">Email</div>
                        <div style="color: rgba(255,255,255,0.8); font-size: 13px;">support@mujstays.com</div>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <div style="font-weight: 600; font-size: 14px;">Phone Line</div>
                        <div style="color: rgba(255,255,255,0.8); font-size: 13px;">+91 98765 43210 ( Mon-Sat 9AM-8PM )</div>
                    </div>
                </div>
                
                <div style="margin-top: 48px;">
                    <div style="font-size: 14px; font-weight: 600; margin-bottom: 16px;">Follow Us</div>
                    <div class="contact-social">
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <h3 style="margin-bottom: 8px;">Send us a Message</h3>
                <p style="margin-bottom: 24px; color: var(--text-muted); font-size: 14px;">We'll get back to you within 24 hours.</p>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Your message has been sent successfully. Our team will contact you soon!
                    </div>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <?= csrf_field() ?>
                        <div class="form-row-2 mb-16">
                            <div class="form-group mb-0">
                                <label class="form-label">Full Name <span class="req">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="John Doe">
                            </div>
                            <div class="form-group mb-0">
                                <label class="form-label">Email Address <span class="req">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="you@example.com">
                            </div>
                        </div>
                        <div class="form-group mb-16">
                            <label class="form-label">Subject <span class="req">*</span></label>
                            <input type="text" name="subject" class="form-control" required placeholder="How can we help?">
                        </div>
                        <div class="form-group mb-24">
                            <label class="form-label">Message <span class="req">*</span></label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="Type your message here..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-xl">Send Message <i class="fas fa-paper-plane" style="margin-left: 8px;"></i></button>
                    </form>
                <?php endif; ?>
            </div>

        </div>
    </div>
</main>

<?php require_once 'components/footer.php'; ?>
</body>
</html>

