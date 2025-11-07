<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriIntel - Smart Agriculture Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">🌱 AgriIntel</div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php?page=login">Login</a></li>
                <li><a href="index.php?page=register">Register</a></li>
            </ul>
        </div>
    </nav>

    <?php include BASE_PATH . '/public/components/language-switcher.php'; ?>

    <section class="hero">
        <div class="container">
            <h1><?= t('smart_agriculture') ?></h1>
            <p><?= t('ai_powered_insights') ?></p>
            <div class="cta-buttons">
                <a href="index.php?page=register" class="btn btn-primary"><?= t('get_started') ?></a>
                <a href="index.php?page=login" class="btn btn-secondary"><?= t('login') ?></a>
            </div>
        </div>
    </section>

    <section class="features">
        <div class="container">
            <h2><?= t('our_services') ?></h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="icon">🌾</div>
                    <h3><?= t('for_farmers') ?></h3>
                    <p><?= t('farmer_desc') ?></p>
                </div>
                <div class="feature-card">
                    <div class="icon">💼</div>
                    <h3><?= t('for_vendors') ?></h3>
                    <p><?= t('vendor_desc') ?></p>
                </div>
                <div class="feature-card">
                    <div class="icon">📊</div>
                    <h3><?= t('ai_analytics') ?></h3>
                    <p><?= t('ai_desc') ?></p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p>&copy; 2025 AgriIntel. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
