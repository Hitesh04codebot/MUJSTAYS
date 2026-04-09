<?php
// faq.php — Frequently Asked Questions
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frequently Asked Questions — MUJSTAYS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php require_once 'components/navbar.php'; ?>

<main class="section">
    <div class="container">
        <div class="section-title">
            <h2>Frequently Asked Questions</h2>
            <p>Everything you need to know about finding and booking a PG near MUJ.</p>
        </div>

        <div style="max-width: 800px; margin: 0 auto;">
            <div class="card mb-16">
                <div class="card-body">
                    <h4 style="margin-bottom: 8px;">Is MUJSTAYS affiliated with Manipal University Jaipur?</h4>
                    <p style="color: var(--text-muted);">MUJSTAYS is an independent platform built exclusively for MUJ students to help them find secure and verified local accommodations. We are not officially part of the university administration.</p>
                </div>
            </div>

            <div class="card mb-16">
                <div class="card-body">
                    <h4 style="margin-bottom: 8px;">How do I know if a PG is safe for girls?</h4>
                    <p style="color: var(--text-muted);">We have a strict verification process for owners. Look for the "KYC Verified" badge. You can also filter for "Girls Only" PGs and check reviews from current student residents.</p>
                </div>
            </div>

            <div class="card mb-16">
                <div class="card-body">
                    <h4 style="margin-bottom: 8px;">Are the prices fixed or negotiable?</h4>
                    <p style="color: var(--text-muted);">The prices listed are provided directly by the owners. While the listed price is usually what you'll pay, some owners may offer small discounts for long-term stays or group bookings. You can chat with them directly on the platform.</p>
                </div>
            </div>

            <div class="card mb-16">
                <div class="card-body">
                    <h4 style="margin-bottom: 8px;">What happens if the PG owner refuses to refund my security deposit?</h4>
                    <p style="color: var(--text-muted);">MUJSTAYS provides a transparent booking record. If you face any issues with deposits, you can report the owner through our "Contact Support" page with proof, and we will assist in mediation or blacklist the property if needed.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'components/footer.php'; ?>
</body>
</html>
