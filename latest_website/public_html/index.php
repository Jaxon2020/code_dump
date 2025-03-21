<?php
// Start the session
session_start();

// Include supabase logic
$supabaseLogicPath = $_SERVER['DOCUMENT_ROOT'] . '/supabase_logic.php';
if (file_exists($supabaseLogicPath)) {
    require_once $supabaseLogicPath;
} else {
    echo "<p>Error: Supabase logic file not found at $supabaseLogicPath.</p>";
}

// Define page-specific variables for head.php
$pageTitle = 'FarmMarket - Your Trusted Farm Animal Marketplace';
$pageSpecificCss = []; // No page-specific CSS for index.php

include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body data-theme="<?php echo htmlspecialchars($_SESSION['theme'] ?? 'original'); ?>">
    <!-- Include Navigation Bar -->
    <?php
    $navPath = $_SERVER['DOCUMENT_ROOT'] . '/nav.php';
    if (file_exists($navPath)) {
        include $navPath;
    } else {
        echo "<p>Error: Navigation file not found at $navPath.</p>";
    }
    ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="featured-animal">
            <img src="/images/horse.jpg" alt="Quarter Horse">
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
        <a href="marketplace.php" class="cta">Browse Animals</a>
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
    <?php
    $footerPath = $_SERVER['DOCUMENT_ROOT'] . '/footer.php';
    if (file_exists($footerPath)) {
        include $footerPath;
    } else {
        echo "<p>Error: Footer file not found at $footerPath.</p>";
    }
    ?>
</body>
</html>