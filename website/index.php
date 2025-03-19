<?php
// Start the PHP file - no need for JavaScript
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmMarket - Your Trusted Farm Animal Marketplace</title>
    <link rel="stylesheet" href="/index.css"> <!-- Path to CSS in public_html -->
</head>
<body>
    <!-- Include Navigation Bar -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/header_n_footer/nav.php'; ?>
    <!-- Using DOCUMENT_ROOT ensures the correct path from public_html -->

    <!-- Hero Section -->
    <section class="hero">
        <div class="featured-animal">
            <img src="/images/horse.jpg" alt="Quarter Horse"> <!-- Updated path to image -->
            <div class="animal-info">
                <h3>Quarter Horse - 5 Years Old</h3>
                <p>$5000</p>
                <div class="tags">
                    <span>Horse</span>
                    <span>Quarter Horse</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Tagline and CTA -->
    <section class="tagline">
        <h1>Your Trusted Farm Animal Marketplace</h1>
        <p>Connect with local farmers and find the perfect animals for your farm.</p>
        <a href="/marketplace/marketplace.php" class="cta">Browse Animals</a> <!-- Changed button to link -->
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="feature-card">
            <h3>Wide Selection</h3>
            <p>Browse through various farm animals from trusted sellers across the country.</p>
        </div>
        <div class="feature-card">
            <h3>Direct Communication</h3>
            <p>Connect directly with sellers through our secure messaging system.</p>
        </div>
        <div class="feature-card">
            <h3>Safe Trading</h3>
            <p>Our platform ensures transparent and secure transactions between buyers and sellers.</p>
        </div>
    </section>

    <!-- Include Footer -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/header_n_footer/footer.php'; ?>
</body>
</html>