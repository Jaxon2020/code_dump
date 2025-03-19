<?php
include '../supabase_logic.php'; // Adjust path if needed
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
        .cart, .profile, .sign-out {
            background: none;
            border: 1px solid #ccc;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
            background-color: #fff;
        }
        .profile:active, .sign-out:active {
            background-color: #007bff;
            color: white;
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
            <li><a href="../profile/profile.php">Test</a></li>
        </ul>
        <div class="nav-actions">
            <input type="text" class="search-bar" placeholder="Search by type, location, or price...">
            <button class="cart">🛒</button>
            <button class="profile" onclick="window.location.href='../profile/profile.php'">Profile</button>
            <?php if (isset($_SESSION['access_token'])): ?>
                <form method="POST" class="auth-form">
                    <input type="hidden" name="signout" value="1">
                    <button type="submit" class="sign-out">Sign Out</button>
                </form>
            <?php endif; ?>
        </div>
    </nav>
    <?php if (!empty($message)): ?>
        <p style="text-align: center;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
</body>
</html>