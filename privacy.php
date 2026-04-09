<?php
// privacy.php — Privacy Policy
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy — MUJSTAYS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once 'components/navbar.php'; ?>

<main class="section">
    <div class="container" style="max-width: 800px;">
        <h1>Privacy Policy</h1>
        <p style="color: var(--text-muted); margin-bottom: 32px;">Last Updated: October 2023</p>

        <section style="margin-bottom: 32px;">
            <h3 style="margin-bottom: 12px;">Data Collection</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">We collect essential info like your name, email, and MUJ student details for verification. Your location data is used only when you click "Auto-Detect My Location" while finding PGs.</p>
        </section>

        <section style="margin-bottom: 32px;">
            <h3 style="margin-bottom: 12px;">How We Use It</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">Your info is strictly used to process your booking requests, allow you to chat with PG owners, and verify your account. We never sell your personal data to outside marketing companies.</p>
        </section>

        <section style="margin-bottom: 32px;">
            <h3 style="margin-bottom: 12px;">Your Control</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">You can update or delete your MUJSTAYS profile at any time through your dashboard. We use industry-standard encryption for your sensitive account credentials.</p>
        </section>
    </div>
</main>

<?php require_once 'components/footer.php'; ?>
</body>
</html>
