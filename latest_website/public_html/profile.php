<?php
// Start the session
session_start();

// Include supabase logic
$supabaseLogicPath = $_SERVER['DOCUMENT_ROOT'] . '/supabase_logic.php';
if (file_exists($supabaseLogicPath)) {
    include_once $supabaseLogicPath;
} else {
    die('Error: Supabase logic file not found at ' . $supabaseLogicPath);
}

// Define page-specific variables for head.php
$pageTitle = 'FarmMarket - Profile';
$pageSpecificCss = ['/css/profile.css']; // Assuming a profile.css exists or will be created

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

    <!-- Profile Section -->
    <section class="profile-content">
        <div class="profile-container">
            <h2>Your Profile</h2>

            <?php if (isset($_SESSION['access_token'])): ?>
                <!-- User is logged in -->
                <div class="profile-info">
                    <p>Welcome back!</p>
                    <p><strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id'] ?? 'Unknown'); ?></p>
                    <form method="POST">
                        <input type="hidden" name="signout" value="1">
                        <button type="submit" class="btn">Sign Out</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- User is not logged in -->
                <div class="auth-section">
                    <h3>Sign In</h3>
                    <form method="POST" class="auth-form">
                        <input type="hidden" name="signin" value="1">
                        <div class="form-group">
                            <label for="signin-email">Email</label>
                            <input type="email" id="signin-email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label for="signin-password">Password</label>
                            <input type="password" id="signin-password" name="password" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn">Sign In</button>
                    </form>

                    <h3>Sign Up</h3>
                    <form method="POST" class="auth-form">
                        <input type="hidden" name="signup" value="1">
                        <div class="form-group">
                            <label for="signup-email">Email</label>
                            <input type="email" id="signup-email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label for="signup-password">Password</label>
                            <input type="password" id="signup-password" name="password" placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="btn">Sign Up</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (!empty($message)): ?>
                <p class="<?php echo strpos($message, 'Failed') !== false ? 'failure' : 'success'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            <?php endif; ?>

            <!-- Additional profile content for logged-in users -->
            <?php if (isset($_SESSION['access_token'])): ?>
                <p>Here you can manage your account, view your listings, and update your preferences.</p>
            <?php endif; ?>
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