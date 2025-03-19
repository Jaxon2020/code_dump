<?php
include 'supabase_logic.php'; // Reference the separate logic file
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supabase Test Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background-color: #0056b3;
        }
        #result {
            margin-top: 10px;
            font-weight: bold;
        }
        .success {
            color: green;
        }
        .failure {
            color: red;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
            background-color: white;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        #createForm {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/header_n_footer/nav.php'; ?>

    <h1>Supabase Test Page</h1>
    
    <!-- Authentication Forms -->
    <form method="POST">
        <button type="submit" name="signup">Sign Up</button>
        <input type="text" name="email" placeholder="Enter your email" required>
        <input type="password" name="password" placeholder="Enter your password" required>
    </form>
    <form method="POST">
        <button type="submit" name="signin">Sign In</button>
        <input type="text" name="email" placeholder="Enter your email" required>
        <input type="password" name="password" placeholder="Enter your password" required>
    </form>
    <form method="POST">
        <button type="submit" name="signout">Sign Out</button>
    </form>
    
    <!-- Create Listing Form -->
    <form id="createForm" method="POST" enctype="multipart/form-data">
        <h3>Create a Listing</h3>
        <input type="hidden" name="create_listing" value="1">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" placeholder="e.g. Chicken - 6 Months Old" required>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" id="price" name="price" placeholder="$" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="type">Type</label>
            <input type="text" id="type" name="type" placeholder="e.g. Chicken" required>
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" placeholder="e.g. Springfield, IL" required>
        </div>
        <div class="form-group">
            <label for="image">Upload Image</label>
            <input type="file" id="image" name="image" accept="image/*" required>
        </div>
        <button type="submit">Create Listing</button>
    </form>

    <!-- Fetch Button and Result -->
    <form method="POST">
        <button type="submit" name="fetch_listings">Fetch Listings</button>
    </form>
    <div id="result">
        <?php
        if (isset($message)) {
            echo "<p class='" . (strpos($message, 'Failed') !== false ? 'failure' : 'success') . "'>$message</p>";
        }
        if (isset($listings) && !empty($listings)) {
            echo '<table><tr><th>ID</th><th>Title</th><th>Price</th><th>Type</th><th>Location</th><th>Image</th><th>Created At</th></tr>';
            foreach ($listings as $row) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                echo '<td>$' . htmlspecialchars(number_format($row['price'], 2)) . '</td>';
                echo '<td>' . htmlspecialchars($row['type']) . '</td>';
                echo '<td>' . htmlspecialchars($row['location']) . '</td>';
                echo '<td><img src="' . htmlspecialchars($row['image_url'] ?? 'N/A') . '" alt="' . htmlspecialchars($row['title']) . '"></td>';
                echo '<td>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['created_at']))) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        ?>
    </div>
</body>
</html>