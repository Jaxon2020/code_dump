<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include supabase logic
require_once $_SERVER['DOCUMENT_ROOT'] . '/supabase_logic.php';

// Initialize listings
$listings = [];

// Generate a form token for the create listing form
if (!isset($_SESSION['form_token'])) {
    $formToken = generateFormToken();
} else {
    $formToken = $_SESSION['form_token'];
}

// Check for a message in the session (from a redirect)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Persist search criteria
$searchCriteria = [
    'animal-type' => $_POST['animal-type'] ?? '',
    'location' => $_POST['location'] ?? '',
    'min-price' => $_POST['min-price'] ?? '',
    'max-price' => $_POST['max-price'] ?? ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_listing'])) {
        $message = createListing($_POST, $_FILES, $_SESSION, $supabaseUrl, $supabaseKey, $storageUrl, $restUrl);
        $_SESSION['message'] = $message;
        unset($_SESSION['form_token']);
        $formToken = generateFormToken();
        header('Location: marketplace.php');
        exit();
    } elseif (isset($_POST['fetch_listings'])) {
        $listings = fetchListings($supabaseKey, $restUrl) ?: [];
        $searchCriteria = ['animal-type' => '', 'location' => '', 'min-price' => '', 'max-price' => '']; // Reset criteria
    } elseif (isset($_POST['search'])) {
        $listings = searchListings($_POST, $supabaseKey, $restUrl) ?: [];
    }
} else {
    $listings = fetchListings($supabaseKey, $restUrl) ?: [];
}

// Define page-specific variables for head.php
$pageTitle = 'FarmMarket - Marketplace';
$pageSpecificCss = ['/css/marketplace.css'];

include $_SERVER['DOCUMENT_ROOT'] . '/head.php';
?>

<body data-theme="<?php echo htmlspecialchars($_SESSION['theme'] ?? 'original'); ?>">
    <!-- Include Navigation Bar -->
    <?php
    $navPath = $_SERVER['DOCUMENT_ROOT'] . '/nav.php';
    if (file_exists($navPath)) {
        require_once $navPath;
    } else {
        echo "<p>Error: Navigation file not found at $navPath.</p>";
    }
    ?>

    <!-- Hero Section -->
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
            
            <!-- Collapsible Create Listing Section -->
            <div class="create-listing-container">
                <button type="button" class="toggle-create-btn btn" onclick="toggleCreateForm()">
                    <span class="btn-icon">+</span> Create Listing
                </button>
                <div class="create-listing-form" id="create-listing-form-container">
                    <h3>Create Item Listing</h3>
                    <?php if (!empty($message)): ?>
                        <p class="<?php echo strpos($message, 'Failed') !== false || strpos($message, 'Invalid') !== false ? 'failure' : 'success'; ?>"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" id="create-listing-form">
                        <input type="hidden" name="create_listing" value="1">
                        <input type="hidden" name="form_token" value="<?php echo htmlspecialchars($formToken); ?>">
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
                        <button type="submit" class="create-btn" id="create-btn">Create Listing</button>
                    </form>
                </div>
            </div>

            <!-- Integrated Filter and Listings Section -->
            <div class="marketplace-integrated">
                <!-- Search Form -->
                <div class="search-form">
                    <h3>Filter Listings</h3>
                    <form method="POST" id="search-form">
                        <input type="hidden" name="search" value="1">
                        <div class="search-section">
                            <label for="animal-type">Animal Type</label>
                            <input type="text" id="animal-type" name="animal-type" placeholder="e.g. Chicken" value="<?php echo htmlspecialchars($searchCriteria['animal-type']); ?>">
                        </div>
                        <div class="search-section">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" placeholder="e.g. Springfield, IL" value="<?php echo htmlspecialchars($searchCriteria['location']); ?>">
                        </div>
                        <div class="price-range">
                            <div class="search-section">
                                <label for="min-price">Min Price</label>
                                <input type="number" id="min-price" name="min-price" placeholder="$" value="<?php echo htmlspecialchars($searchCriteria['min-price']); ?>">
                            </div>
                            <div class="search-section">
                                <label for="max-price">Max Price</label>
                                <input type="number" id="max-price" name="max-price" placeholder="$" value="<?php echo htmlspecialchars($searchCriteria['max-price']); ?>">
                            </div>
                        </div>
                        <div class="search-buttons">
                            <button type="submit" class="search-btn btn">Search</button>
                            <button type="button" class="reset-btn btn" onclick="document.getElementById('search-form').reset(); document.getElementById('search-form').submit();">Reset</button>
                        </div>
                    </form>
                </div>

                <!-- Listings Container -->
                <div class="listings-container">
                    <h3>Available Listings</h3>
                    <?php if (!empty($message) && (strpos($message, 'Search Failed') !== false || strpos($message, 'Found') !== false)): ?>
                        <p class="<?php echo strpos($message, 'Failed') !== false ? 'failure' : 'success'; ?>"><?php echo htmlspecialchars($message); ?></p>
                    <?php endif; ?>
                    <?php if (empty($listings)): ?>
                        <p class="no-results">No listings found. Try adjusting your search criteria or fetch listings.</p>
                    <?php else: ?>
                        <div class="card-grid">
                            <?php foreach ($listings as $listing): ?>
                                <div class="card">
                                    <img src="<?php echo htmlspecialchars($listing['image_url'] ?? '/images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
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
            </div>

            <!-- Fetch Section -->
            <div class="fetch-section">
                <form method="POST">
                    <input type="hidden" name="fetch_listings" value="1">
                    <button type="submit" class="fetch-btn btn">Fetch Listings</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Include Footer -->
    <?php
    $footerPath = $_SERVER['DOCUMENT_ROOT'] . '/footer.php';
    if (file_exists($footerPath)) {
        require_once $footerPath;
    } else {
        echo "<p>Error: Footer file not found at $footerPath.</p>";
    }
    ?>

    <script>
        function toggleCreateForm() {
            const formContainer = document.getElementById('create-listing-form-container');
            formContainer.classList.toggle('active');
        }

        document.getElementById('create-listing-form').addEventListener('submit', function() {
            const createBtn = document.getElementById('create-btn');
            createBtn.disabled = true;
            createBtn.textContent = 'Creating...';
        });
    </script>
</body>
</html>