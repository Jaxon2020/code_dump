<?php
include '../supabase_logic.php'; // Adjust path if needed (e.g., '../../supabase_logic.php' if in a subdirectory)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation</title>
    <style>
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 50px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }
        nav ul li a {
            text-decoration: none;
            color: #666;
            font-size: 16px;
        }
        .nav-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .search-bar {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .cart, .sign-up, .sign-in, .profile, .sign-out {
            background: none;
            border: 1px solid #ccc;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .sign-up, .sign-in, .profile, .sign-out {
            background-color: #fff;
        }
        .sign-up:active, .sign-in:active, .profile:active, .sign-out:active {
            background-color: #007bff;
            color: white;
        }
        .auth-form {
            display: inline-flex;
            gap: 5px;
            align-items: center;
        }
        .auth-form input[type="email"], .auth-form input[type="password"] {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">FarmMarket</div>
        <ul>
            <li><a href="../index.php">Home</a></li>
            <li><a href="../marketplace/marketplace.php">Market Place</a></li>
            <li><a href="../about/about.php">About Us</a></li>
            <li><a href="../agreement/agreement.php">Agreement</a></li>
            <li><a href="../test/supabase_test.php">Test</a></li>
        </ul>
        <div class="nav-actions">
            <input type="text" class="search-bar" placeholder="Search by type, location, or price...">
            <button class="cart">🛒</button>
            <?php if (isset($_SESSION['access_token'])): ?>
                <button class="profile" onclick="window.location.href='../profile.php'">Profile</button>
                <form method="POST" class="auth-form">
                    <input type="hidden" name="signout" value="1">
                    <button type="submit" class="sign-out">Sign Out</button>
                </form>
            <?php else: ?>
                <form method="POST" class="auth-form">
                    <input type="hidden" name="signup" value="1">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" class="sign-up">Sign Up</button>
                </form>
                <form method="POST" class="auth-form">
                    <input type="hidden" name="signin" value="1">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" class="sign-in">Sign In</button>
                </form>
            <?php endif; ?>
        </div>
    </nav>
    <?php if (!empty($message)): ?>
        <p style="text-align: center;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
</body>
</html>