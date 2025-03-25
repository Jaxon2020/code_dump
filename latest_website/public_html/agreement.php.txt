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
$pageTitle = 'FarmMarket - Service Agreement';
$pageSpecificCss = ['/css/agreement.css'];

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
            <img src="/images/chicken.jpg" alt="Featured Chicken">
            <div class="animal-info">
                <h3>Our Commitment - Safe Trading</h3>
                <p>Learn about our terms and guidelines</p>
                <div class="tags">
                    <span>Safety</span>
                    <span>Agreement</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Service Agreement Section -->
    <section class="agreement-content">
        <div class="agreement-container">
            <h2>Service Agreement <a href="#" class="download-btn">Download</a></h2>

            <h3>1. Introduction</h3>
            <p>This Service Agreement ("Agreement") is entered into between FarmMarket ("Platform", "we", "us") and the users ("User", "you") of the platform.</p>

            <h3>2. Platform Role</h3>
            <p>FarmMarket serves as a platform for connecting buyers and sellers of farm animals. We do not:</p>
            <ul>
                <li>Participate in any transaction between users</li>
                <li>Take possession of any animals listed on the platform</li>
                <li>Guarantee the quality or health of any animals</li>
                <li>Handle payments between parties</li>
            </ul>

            <h3>3. User Responsibilities</h3>
            <p>Users agree to:</p>
            <ul>
                <li>Provide accurate information in listings</li>
                <li>Comply with all applicable laws and regulations</li>
                <li>Conduct transactions safely and professionally</li>
                <li>Report any suspicious activity to the platform</li>
            </ul>

            <h3>4. Liability Limitations</h3>
            <p>FarmMarket is not liable for:</p>
            <ul>
                <li>The condition or quality of animals listed</li>
                <li>The accuracy of listing information</li>
                <li>Disputes between users</li>
                <li>Any losses incurred during transactions</li>
            </ul>

            <h3>5. Safety Guidelines</h3>
            <p>We recommend:</p>
            <ul>
                <li>Meeting in public places when possible</li>
                <li>Inspecting animals before purchase</li>
                <li>Getting health certificates when applicable</li>
                <li>Using secure payment methods</li>
            </ul>
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