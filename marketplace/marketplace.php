<?php
include '../supabase_logic.php'; // Reference the logic file with the correct path

// Call functions from supabase_logic.php explicitly if POST actions occur
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $supabaseUrl, $supabaseKey, $storageUrl, $restUrl; // Access variables from supabase_logic.php

    if (isset($_POST['create_listing'])) {
        createListing($_POST, $_FILES, $_SESSION, $supabaseUrl, $supabaseKey, $storageUrl, $restUrl);
    } elseif (isset($_POST['fetch_listings'])) {
        fetchListings($supabaseKey, $restUrl);
    } elseif (isset($_POST['search'])) {
        searchListings($_POST, $supabaseKey, $restUrl);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FarmMarket - Marketplace</title>
    <link rel="stylesheet" href="/marketplace/marketplace.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/header_n_footer/nav.php'; ?>

    <section class="hero">
        <div class="featured-animal">
            <img src="/images/chicken.jpg" alt="Featured Chicken">
            <div class="animal-info">
                <h3>Featured Chicken - 6 Months Old</h3>
                <p>$20</p>
                <div class="tags">
                    <span>Chicken</span>
                    <span>Laying Hen</span>
                </div>
            </div>
        </div>
    </section>

    <section class="marketplace-search">
        <div class="marketplace-content">
            <h2>Marketplace</h2>

            <div class="create-listing-form">
                <h3>Create Item Listing</h3>
                <?php if (!empty($message)): ?>
                    <p class="<?php echo strpos($message, 'Failed') !== false ? 'failure' : 'success'; ?>"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="create_listing" value="1">
                    <div class="form-group">
                        <label for="listing-title">Title</label>
                        <input type="text" id="listing-title" name="title" placeholder="e.g. Chicken - 6 Months Old" required>
                    </div>
                    <div class="form-group">
                        <label for="listing-price">Price</label>
                        <input type="number" id="listing-price" name="price" placeholder="$" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="listing-type">Type</label>
                        <input type="text" id="listing-type" name="type" placeholder="e.g. Chicken" required>
                    </div>
                    <div class="form-group">
                        <label for="listing-location">Location</label>
                        <input type="text" id="listing-location" name="location" placeholder="e.g. Springfield, IL" required>
                    </div>
                    <div class="form-group image-upload">
                        <label for="listing-image">Upload Image</label>
                        <input type="file" id="listing-image" name="image" accept="image/*" required>
                    </div>
                    <button type="submit" class="create-btn">Create Listing</button>
                </form>
            </div>

            <div class="search-form">
                <form method="POST">
                    <input type="hidden" name="search" value="1">
                    <div class="search-section">
                        <label>Animal Type</label>
                        <input type="text" name="animal-type" placeholder="e.g. Chicken">
                    </div>
                    <div class="search-section">
                        <label>Location</label>
                        <input type="text" name="location" placeholder="e.g. Springfield, IL">
                    </div>
                    <div class="search-section">
                        <label>Min Price</label>
                        <input type="number" name="min-price" placeholder="$">
                    </div>
                    <div class="search-section">
                        <label>Max Price</label>
                        <input type="number" name="max-price" placeholder="$">
                    </div>
                    <button type="submit" class="search-btn">Search</button>
                    <button type="reset" class="reset-btn">Reset</button>
                </form>
            </div>

            <div class="listings-container">
                <h3>Available Listings</h3>
                <?php if (empty($listings)): ?>
                    <p class="no-results">No listings found. Try adjusting your search criteria or fetch listings.</p>
                <?php else: ?>
                    <div class="card-grid">
                        <?php foreach ($listings as $listing): ?>
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($listing['image_url'] ?? 'N/A'); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>" style="width:100%">
                                <h4><?php echo htmlspecialchars($listing['title']); ?></h4>
                                <p class="price">$<?php echo htmlspecialchars(number_format($listing['price'], 2)); ?></p>
                                <p><strong>Type:</strong> <?php echo htmlspecialchars($listing['type']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($listing['location']); ?></p>
                                <p><button class="view-details">View Details</button></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="fetch-section">
                <form method="POST">
                    <button type="submit" name="fetch_listings" class="fetch-btn">Fetch Listings</button>
                </form>
                <?php if (isset($message) && strpos($message, 'Fetch') !== false): ?>
                    <p class="<?php echo strpos($message, 'Failed') !== false ? 'failure' : 'success'; ?>"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/header_n_footer/footer.php'; ?>
</body>
</html>