<?php
// Enable error reporting for debugging (remove in production)
#ini_set('display_errors', 1);
#ini_set('display_startup_errors', 1);
#error_reporting(E_ALL);

// Include supabase logic
if (file_exists('../supabase_logic.php')) {
    include_once '../supabase_logic.php';
} else {
    die('Error: supabase_logic.php not found.');
}

// Handle POST requests for signin/signup/signout are already in supabase_logic.php
?>

<!DOCTYPE html>
<html lang="en">
        <?php
    // Include navigation
    $navPath = $_SERVER['DOCUMENT_ROOT'] . '/header_n_footer/nav.php';
    if (file_exists($navPath)) {
        include_once $navPath; // Use include_once here too for consistency
    } else {
        echo '<p>Error: Navigation file not found.</p>';
    }
    ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmMarket - Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"], input[type="password"], input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .failure {
            background-color: #f8d7da;
            color: #721c24;
        }
        .profile-info {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>FarmMarket Profile</h1>

        <?php if (isset($_SESSION['access_token'])): ?>
            <!-- User is logged in -->
            <div class="profile-info">
                <h2>Welcome!</h2>
                <p><strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id'] ?? 'Unknown'); ?></p>
                <form method="POST">
                    <input type="hidden" name="signout" value="1">
                    <button type="submit">Sign Out</button>
                </form>
            </div>
        <?php else: ?>
            <!-- User is not logged in -->
            <h2>Sign In</h2>
            <form method="POST">
                <input type="hidden" name="signin" value="1">
                <div class="form-group">
                    <label for="signin-email">Email</label>
                    <input type="email" id="signin-email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="signin-password">Password</label>
                    <input type="password" id="signin-password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit">Sign In</button>
            </form>

            <h2>Sign Up</h2>
            <form method="POST">
                <input type="hidden" name="signup" value="1">
                <div class="form-group">
                    <label for="signup-email">Email</label>
                    <input type="email" id="signup-email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="signup-password">Password</label>
                    <input type="password" id="signup-password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit">Sign Up</button>
            </form>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'Failed') !== false ? 'failure' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>