<?php
// about.php — About Us page
session_start();
require_once 'config/config.php';
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us — MUJSTAYS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .about-hero {
            background: linear-gradient(135deg, var(--primary) 0%, #16537e 50%, var(--accent) 100%);
            color: #fff;
            padding: 80px 0;
            text-align: center;
        }
        .about-hero h1 {
            color: #fff;
            font-size: 3rem;
            margin-bottom: 16px;
        }
        .about-hero p {
            color: rgba(255,255,255,0.8);
            font-size: 1.15rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: center;
        }
        .about-img {
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }
        .about-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .value-card {
            background: var(--card-bg);
            padding: 32px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border);
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        .value-icon {
            width: 64px;
            height: 64px;
            background: #EBF5FB;
            color: var(--accent);
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 28px;
            margin: 0 auto 20px;
        }
        @media (max-width: 768px) {
            .about-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php require_once 'components/navbar.php'; ?>

<header class="about-hero">
    <div class="container">
        <h1>Redefining Student Housing</h1>
        <p>MUJSTAYS is built exclusively for students of Manipal University Jaipur to safely discover, compare, and book premium PGs.</p>
    </div>
</header>

<main>
    <section class="section">
        <div class="container about-grid">
            <div>
                <h2 style="margin-bottom: 24px;">Our Story</h2>
                <p>Finding the right accommodation near campus shouldn't be a gamble. We realized that students were spending weeks hopping from one PG to another, dealing with hidden fees, unverified landlords, and mismatched roommates.</p>
                <p>That's why we created <strong>MUJSTAYS</strong>. Our goal is to bring transparency, ease, and security to student housing. Every property listed is vetted, giving both students and PG owners peace of mind.</p>
                <a href="<?= BASE_URL ?>/explore.php" class="btn btn-primary mt-16">Explore Verified PGs</a>
            </div>
            <div class="about-img">
                <img src="<?= BASE_URL ?>/assets/img/2724272.jpg" alt="About MUJSTAYS">
            </div>

        </div>
    </section>

    <section class="section" style="background: var(--bg2);">
        <div class="container">
            <div class="section-title">
                <h2>Our Core Values</h2>
                <p>We're driven by the mission to make your university living experience exceptional.</p>
            </div>
            
            <div class="row" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Trust & Safety</h3>
                    <p>Every PG is thoroughly vetted before listing. No surprises, no scams. Just secure homes.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-handshake"></i></div>
                    <h3>Transparency</h3>
                    <p>Clear pricing, defined amenities, and upfront reviews from actual student residents.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-home"></i></div>
                    <h3>Quality Living</h3>
                    <p>We ensure properties maintain high standards of cleanliness, food quality, and Wi-Fi speeds.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container text-center">
            <h2>Ready to find your second home?</h2>
            <p style="max-width: 500px; margin: 16px auto 32px;">Join thousands of MUJ students who use our platform to lock in the absolute best deals around the campus.</p>
            <a href="<?= BASE_URL ?>/explore.php" class="btn btn-primary btn-xl">Start Browsing</a>
            <a href="<?= BASE_URL ?>/contact.php" class="btn btn-secondary btn-xl" style="margin-left: 12px;">Get In Touch</a>
        </div>
    </section>
</main>

<?php require_once 'components/footer.php'; ?>
</body>
</html>

