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
$pageTitle = 'FarmMarket - About Us';
$pageSpecificCss = ['/css/about.css'];

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
            <img src="/images/horse.jpg" alt="Featured Horse">
            <div class="animal-info">
                <h3>Our Community - Thriving Together</h3>
                <p>Join us in connecting farmers and buyers</p>
                <div class="tags">
                    <span>Community</span>
                    <span>FarmMarket</span>
                </div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="about-content">
        <div class="about-container">
            <h2>About FarmMarket</h2>
            <p>FarmMarket is your trusted platform for buying and selling farm animals. We connect farmers, breeders, and buyers in a secure and transparent marketplace environment.</p>

            <h3>Our Mission</h3>
            <p>Our mission is to create a reliable and efficient marketplace that serves the agricultural community. We strive to provide a platform where:</p>
            <ul>
                <li>Sellers can reach a wider audience of potential buyers</li>
                <li>Buyers can find exactly what they’re looking for</li>
                <li>All transactions are conducted safely and transparently</li>
                <li>The farming community can thrive and grow</li>
            </ul>

            <h3>How It Works</h3>
            <div class="how-it-works">
                <div class="work-step">
                    <h4>For Sellers</h4>
                    <p>Create listings for your farm animals, connect with potential buyers, and manage your sales all in one place.</p>
                </div>
                <div class="work-step">
                    <h4>For Buyers</h4>
                    <p>Browse listings, filter by your preferences, and connect with sellers to find your perfect match.</p>
                </div>
                <div class="work-step">
                    <h4>Safe Trading</h4>
                    <p>Our platform provides the tools and guidelines for safe and secure transactions between parties.</p>
                </div>
            </div>

            <h3>Contact Us</h3>
            <p>Have questions or suggestions? We’d love to hear from you. Contact our support team at <a href="mailto:support@farmmarket.com">support@farmmarket.com</a> or call us at (555) 123-4567.</p>
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