

<?php
session_start(); // Initiates a session to maintain user state across pages

// Theme handling: manages the visual theme of the application
if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'farmed'; // Sets default theme if not already set
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $_SESSION['theme'] = $_POST['theme']; // Updates theme based on user selection
    header('Location: ' . $_SERVER['PHP_SELF']); // Redirects to prevent form resubmission
    exit();
}

// Prevents redefinition of constants and functions if this file is included multiple times
if (!defined('SUPABASE_LOGIC_LOADED')) {
    define('SUPABASE_LOGIC_LOADED', true);

    // Supabase configuration: connection details for Supabase services
    $supabaseUrl = 'https://owvtdphfvwmvcnstlfnz.supabase.co'; // Base URL for Supabase instance
    $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im93dnRkcGhmdndtdmNuc3RsZm56Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDIyNTUwMjEsImV4cCI6MjA1NzgzMTAyMX0.0ohoAWFipfpWGDKTyPAOlo-IoCJtQTCJEG7ucnWROaE'; // API key for authentication (anonymized for example)
    $authUrl = $supabaseUrl . '/auth/v1'; // Authentication endpoint
    $restUrl = $supabaseUrl . '/rest/v1'; // REST API endpoint
    $storageUrl = $supabaseUrl . '/storage/v1'; // Storage API endpoint

    // Global variables for application state
    $message = ''; // Stores feedback messages for the user
    $listings = []; // Holds listing data retrieved from the database

    // Generic HTTP request function for interacting with Supabase APIs
    if (!function_exists('makeHttpRequest')) {
        function makeHttpRequest($url, $method, $headers = [], $data = null) {
            $options = [
                'http' => [
                    'method' => $method, // HTTP method (GET, POST, etc.)
                    'header' => implode("\r\n", $headers), // Concatenates headers into a string
                ]
            ];
            if ($data !== null) {
                $options['http']['content'] = $data; // Adds request body if provided
            }
            $context = stream_context_create($options); // Creates a stream context for the request
            $result = @file_get_contents($url, false, $context); // Makes the request, suppressing warnings
            return $result ? json_decode($result, true) : false; // Returns decoded JSON or false on failure
        }
    }

    // Generates common HTTP headers for Supabase requests
    if (!function_exists('getCommonHeaders')) {
        function getCommonHeaders($supabaseKey, $authToken = null, $extraHeaders = []) {
            $headers = [
                'apikey: ' . $supabaseKey, // API key for Supabase authentication
                'Content-Type: application/json' // Specifies JSON content type
            ];
            if ($authToken) {
                $headers[] = 'Authorization: Bearer ' . $authToken; // Adds user auth token if provided
            }
            return array_merge($headers, $extraHeaders); // Combines with any additional headers
        }
    }

    // Generates a unique token to prevent CSRF attacks in forms
    if (!function_exists('generateFormToken')) {
        function generateFormToken() {
            if (!isset($_SESSION['form_token'])) {
                $token = bin2hex(random_bytes(16)); // Creates a 32-character random hex string
                $_SESSION['form_token'] = $token; // Stores it in the session
            }
            return $_SESSION['form_token'];
        }
    }

    // Validates the form token to ensure legitimate submissions
    if (!function_exists('validateFormToken')) {
        function validateFormToken($token) {
            if (isset($_SESSION['form_token']) && $_SESSION['form_token'] === $token) {
                unset($_SESSION['form_token']); // Removes token after use to prevent reuse
                return true;
            }
            return false; // Returns false if token is invalid or missing
        }
    }

    // Handles user signup with Supabase authentication
    if (!function_exists('handleSignup')) {
        function handleSignup($postData, $supabaseKey, $authUrl) {
            global $message;
            $email = $postData['email'] ?? '';
            $password = $postData['password'] ?? '';
            if (empty($email) || empty($password)) {
                $message = "Sign Up Failed: Email and password are required.";
                return;
            }
            $data = json_encode(['email' => $email, 'password' => $password]); // Prepares signup data
            $headers = getCommonHeaders($supabaseKey);
            $response = makeHttpRequest($authUrl . '/signup', 'POST', $headers, $data);
            if ($response && isset($response['error'])) {
                $message = "Sign Up Failed: " . $response['error']['message'];
            } else {
                $message = "Sign up successful! Check your email for confirmation.";
            }
        }
    }

    // Handles user signin with Supabase authentication
    if (!function_exists('handleSignin')) {
        function handleSignin($postData, $supabaseKey, $authUrl) {
            global $message;
            $email = $postData['email'] ?? '';
            $password = $postData['password'] ?? '';
            if (empty($email) || empty($password)) {
                $message = "Sign In Failed: Email and password are required.";
                return;
            }
            $data = json_encode(['email' => $email, 'password' => $password]);
            $headers = getCommonHeaders($supabaseKey);
            $response = makeHttpRequest($authUrl . '/token?grant_type=password', 'POST', $headers, $data);
            if ($response && isset($response['error'])) {
                $message = "Sign In Failed: " . $response['error']['message'];
            } else if ($response) {
                $_SESSION['access_token'] = $response['access_token']; // Stores user token
                $_SESSION['user_id'] = $response['user']['id'] ?? ''; // Stores user ID
                $message = "Sign in successful!";
            } else {
                $message = "Sign In Failed: Unknown error";
            }
        }
    }

    // Handles user signout
    if (!function_exists('handleSignout')) {
        function handleSignout() {
            global $message;
            unset($_SESSION['access_token']); // Removes access token
            unset($_SESSION['user_id']); // Removes user ID
            $message = "Signed out successfully.";
        }
    }

    // Creates a new listing with image upload
    if (!function_exists('createListing')) {
        function createListing($postData, $files, $session, $supabaseUrl, $supabaseKey, $storageUrl, $restUrl) {
            global $message;
            $token = $postData['form_token'] ?? '';
            if (!validateFormToken($token)) {
                $message = "Invalid or duplicate form submission.";
                return;
            }

            if (!isset($session['access_token'])) {
                $message = "Please sign in to create a listing.";
                return;
            }

            $title = $postData['title'] ?? '';
            $price = $postData['price'] ?? '';
            $type = $postData['type'] ?? '';
            $location = $postData['location'] ?? '';
            $imageFile = $files['image'] ?? null;
            if (empty($title) || empty($price) || empty($type) || empty($location) || !$imageFile) {
                $message = "Create Failed: All fields are required.";
                return;
            }

            if ($imageFile['error'] === UPLOAD_ERR_OK) {
                $fileName = time() . '_' . $imageFile['name']; // Creates unique filename
                $tmpName = $imageFile['tmp_name'];
                $uploadUrl = $storageUrl . '/object/listings-images/public/' . $fileName;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $uploadUrl);
                curl_setopt($ch, CURLOPT_PUT, true); // Uses PUT for file upload
                curl_setopt($ch, CURLOPT_INFILE, fopen($tmpName, 'r')); // Reads file from temp location
                curl_setopt($ch, CURLOPT_INFILESIZE, filesize($tmpName));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Authorization: Bearer ' . $session['access_token'],
                    'Content-Type: application/octet-stream'
                ]);
                $uploadSuccess = curl_exec($ch);
                $curlError = curl_error($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if (!$uploadSuccess || $httpCode >= 400) {
                    $message = "Image upload failed: " . ($curlError ?: "HTTP $httpCode");
                    return;
                }

                $imageUrl = $supabaseUrl . '/storage/v1/object/public/listings-images/public/' . $fileName;
                $data = json_encode([
                    'title' => $title,
                    'price' => floatval($price),
                    'type' => $type,
                    'location' => $location,
                    'image_url' => $imageUrl,
                    'user_id' => $session['user_id'] ?? ''
                ]);
                $headers = getCommonHeaders($supabaseKey, $session['access_token']);
                $response = makeHttpRequest($restUrl . '/listings', 'POST', $headers, $data);
                if ($response === false) {
                    $message = "Create Failed: Unable to connect to Supabase.";
                    return;
                }
                if (isset($response['error'])) {
                    $message = "Create Failed: " . $response['error']['message'];
                    return;
                }
                $message = "Listing created successfully!";
            } else {
                $message = "Image upload failed: " . $imageFile['error'];
            }
        }
    }

    // Fetches all listings from Supabase, ordered by creation date
    if (!function_exists('fetchListings')) {
        function fetchListings($supabaseKey, $restUrl) {
            global $message, $listings;
            $headers = getCommonHeaders($supabaseKey, null, ['Prefer: return=representation']);
            $response = makeHttpRequest($restUrl . '/listings?order=created_at.desc', 'GET', $headers);
            if ($response === false || (is_array($response) && isset($response['error']))) {
                $message = "Fetch Failed: " . ($response['error']['message'] ?? 'Unknown error');
                return [];
            }
            $listings = $response; // Stores fetched listings globally
            return $response;
        }
    }

    // Searches listings based on user-provided criteria
    if (!function_exists('searchListings')) {
        function searchListings($postData, $supabaseKey, $restUrl) {
            global $message, $listings;
            $queryParts = ['order=created_at.desc']; // Default sort by creation date descending
        
            if (!empty($postData['animal-type'])) {
                $animalType = trim($postData['animal-type']);
                $queryParts[] = 'type=ilike.' . rawurlencode("%$animalType%"); // Case-insensitive partial match
            }
        
            if (!empty($postData['location'])) {
                $location = trim($postData['location']);
                $queryParts[] = 'location=ilike.' . rawurlencode("%$location%");
            }
        
            if (!empty($postData['min-price'])) {
                $minPrice = filter_var($postData['min-price'], FILTER_VALIDATE_FLOAT);
                if ($minPrice === false || $minPrice < 0) {
                    $message = "Search Failed: Invalid minimum price.";
                    return [];
                }
                $queryParts[] = 'price=gte.' . rawurlencode($minPrice); // Greater than or equal to
            }
        
            if (!empty($postData['max-price'])) {
                $maxPrice = filter_var($postData['max-price'], FILTER_VALIDATE_FLOAT);
                if ($maxPrice === false || $maxPrice < 0) {
                    $message = "Search Failed: Invalid maximum price.";
                    return [];
                }
                $queryParts[] = 'price=lte.' . rawurlencode($maxPrice); // Less than or equal to
            }
        
            if (isset($minPrice) && isset($maxPrice) && $minPrice > $maxPrice) {
                $message = "Search Failed: Minimum price cannot exceed maximum price.";
                return [];
            }
        
            $query = '?' . implode('&', $queryParts); // Builds the query string
            $headers = getCommonHeaders($supabaseKey, null, ['Prefer: return=representation']);
            $response = makeHttpRequest($restUrl . '/listings' . $query, 'GET', $headers);
        
            if ($response === false) {
                $message = "Search Failed: Unable to connect to Supabase.";
                return [];
            }
            if (isset($response['error'])) {
                $message = "Search Failed: " . $response['error']['message'];
                return [];
            }
        
            $listings = $response;
            $message = empty($listings) ? "No listings found matching your criteria." : "Found " . count($listings) . " listing(s).";
            return $listings;
        }
    }
}

// Processes POST requests to trigger appropriate actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signup'])) {
        handleSignup($_POST, $supabaseKey, $authUrl); // New user registration
    } elseif (isset($_POST['signin'])) {
        handleSignin($_POST, $supabaseKey, $authUrl); // User login
    } elseif (isset($_POST['signout'])) {
        handleSignout(); // User logout
    } elseif (isset($_POST['create_listing'])) {
        createListing($_POST, $_FILES, $_SESSION, $supabaseUrl, $supabaseKey, $storageUrl, $restUrl); // New listing creation
    } elseif (isset($_POST['fetch_listings'])) {
        fetchListings($supabaseKey, $restUrl); // Retrieve all listings
    } elseif (isset($_POST['search'])) {
        searchListings($_POST, $supabaseKey, $restUrl); // Search listings with filters
    }
}
?>