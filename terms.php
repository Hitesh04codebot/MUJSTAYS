<?php
// terms.php — Terms and Conditions
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions — MUJSTAYS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once 'components/navbar.php'; ?>

<main class="section">
    <div class="container" style="max-width: 800px;">
        <h1>Terms and Conditions</h1>
        <p style="color: var(--text-muted); margin-bottom: 32px;">Last Updated: October 2023</p>

        <section style="margin-bottom: 32px;">
            <h3 style="margin-bottom: 12px;">1. Introduction</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">By accessing MUJSTAYS, you agree to follow our terms. Our service is a directory platform designed to connect students with local PG owners. We do not own any properties ourselves.</p>
        </section>

        <section style="margin-bottom: 32px;">
            <h3 style="margin-bottom: 12px;">2. User Responsibility</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">Users are responsible for confirming all PG details before making payments. We recommend visiting any property in person before final commitment. Students must provide authentic information during registration.</p>
        </section>

        <section style="margin-bottom: 32px;">
            <h3 style="margin-bottom: 12px;">3. Booking Payments</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">Payment transactions conducted through the site are subject to their respective refund policies. MUJSTAYS may facilitate communication between parties but is not liable for private financial disputes.</p>
        </section>

        <section style="margin-bottom: 32px;">
            <h3 style="margin-bottom: 12px;">4. Prohibited Activities</h3>
            <p style="color: var(--text-muted); line-height: 1.6;">Posting false listings, spamming users, and attempting to bypass platform security measures will result in an immediate and permanent ban from the MUJSTAYS network.</p>
        </section>
    </div>
</main>

<?php require_once 'components/footer.php'; ?>
</body>
</html>
