<?php
// header_n_footer/nav.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/supabase_logic.php';

// Note: This file assumes that the parent page includes /index.css in its <head> for styling.
?>

<nav class="navbar">
    <div class="logo">
        <a href="/index.php">FarmMarket</a>
    </div>
    <ul class="nav-links">
        <li><a href="/index.php">Home</a></li>
        <li><a href="/marketplace.php">Market Place</a></li>
        <li><a href="/about.php">About Us</a></li>
        <li><a href="/agreement.php">Agreement</a></li>
    </ul>
    <div class="nav-actions">
        <button class="profile" onclick="window.location.href='/profile.php'">Profile</button>
        <?php if (isset($_SESSION['access_token'])): ?>
            <form method="POST" class="auth-form">
                <input type="hidden" name="signout" value="1">
                <button type="submit" class="sign-out">Sign Out</button>
            </form>
        <?php endif; ?>
        <!-- Theme Switcher -->
        <div class="theme-switcher">
            <form method="POST">
                <label for="theme-select">Theme:</label>
                <select id="theme-select" name="theme" onchange="this.form.submit()">
                    <option value="original" <?php echo $_SESSION['theme'] === 'original' ? 'selected' : ''; ?>>Original</option>
                    <option value="farmed" <?php echo $_SESSION['theme'] === 'farmed' ? 'selected' : ''; ?>>Farmed</option>
                    <option value="dark" <?php echo $_SESSION['theme'] === 'dark' ? 'selected' : ''; ?>>Dark Mode</option>
                    <option value="vintage" <?php echo $_SESSION['theme'] === 'vintage' ? 'selected' : ''; ?>>Vintage Farm</option>
                    <option value="modern" <?php echo $_SESSION['theme'] === 'modern' ? 'selected' : ''; ?>>Modern Farm</option>
                </select>
            </form>
        </div>
    </div>
</nav>
<?php if (!empty($message)): ?>
    <p class="nav-message"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>