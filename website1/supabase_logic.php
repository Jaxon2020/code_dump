<?php
session_start(); // Start session to track user authentication

// Theme handling
if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'farmed'; // Default to farmed theme
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme'])) {
    $_SESSION['theme'] = $_POST['theme'];
    // Redirect to the current page to avoid form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Prevent redefinition if this file is included multiple times
if (!defined('SUPABASE_LOGIC_LOADED')) {
    define('SUPABASE_LOGIC_LOADED', true);

    // Supabase credentials
    $supabaseUrl = 'https://owvtdphfvwmvcnstlfnz.supabase.co';
    $supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im93dnRkcGhmdndtdmNuc3RsZm56Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDIyNTUwMjEsImV4cCI6MjA1NzgzMTAyMX0.0ohoAWFipfpWGDKTyPAOlo-IoCJtQTCJEG7ucnWROaE';
    $authUrl = $supabaseUrl . '/auth/v1';
    $restUrl = $supabaseUrl . '/rest/v1';
    $storageUrl = $supabaseUrl . '/storage/v1';

    // Default variables
    $message = '';
    $listings = [];

    // Helper function for making HTTP requests
    if (!function_exists('makeHttpRequest')) {
        function makeHttpRequest($url, $method, $headers = [], $data = null) {
            $options = [
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", $headers),
                ]
            ];
            if ($data !== null) {
                $options['http']['content'] = $data;
            }
            $context = stream_context_create($options);
            $result = @file_get_contents($url, false, $context);
            return $result ? json_decode($result, true) : false;
        }
    }

    // Helper function for common headers
    if (!function_exists('getCommonHeaders')) {
        function getCommonHeaders($supabaseKey, $authToken = null, $extraHeaders = []) {
            $headers = [
                'apikey: ' . $supabaseKey,
                'Content-Type: application/json'
            ];
            if ($authToken) {
                $headers[] = 'Authorization: Bearer ' . $authToken;
            }
            return array_merge($headers, $extraHeaders);
        }
    }

    // Function to generate a unique token for form submission
    if (!function_exists('generateFormToken')) {
        function generateFormToken() {
            if (!isset($_SESSION['form_token'])) {
                $token = bin2hex(random_bytes(16));
                $_SESSION['form_token'] = $token;
            }
            return $_SESSION['form_token'];
        }
    }

    // Function to validate the form token
    if (!function_exists('validateFormToken')) {
        function validateFormToken($token) {
            if (isset($_SESSION['form_token']) && $_SESSION['form_token'] === $token) {
                unset($_SESSION['form_token']); // Clear the token after use
                return true;
            }
            return false;
        }
    }

    // Function to handle signup
    if (!function_exists('handleSignup')) {
        function handleSignup($postData, $supabaseKey, $authUrl) {
            global $message;
            $email = $postData['email'] ?? '';
            $password = $postData['password'] ?? '';
            if (empty($email) || empty($password)) {
                $message = "Sign Up Failed: Email and password are required.";
                return;
            }
            $data = json_encode(['email' => $email, 'password' => $password]);
            $headers = getCommonHeaders($supabaseKey);
            $response = makeHttpRequest($authUrl . '/signup', 'POST', $headers, $data);
            if ($response && isset($response['error'])) {
                $message = "Sign Up Failed: " . $response['error']['message'];
            } else {
                $message = "Sign up successful! Check your email for confirmation.";
            }
        }
    }

    // Function to handle signin
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
                $_SESSION['access_token'] = $response['access_token'];
                $_SESSION['user_id'] = $response['user']['id'] ?? '';
                $message = "Sign in successful!";
            } else {
                $message = "Sign In Failed: Unknown error";
            }
        }
    }

    // Function to handle signout
    if (!function_exists('handleSignout')) {
        function handleSignout() {
            global $message;
            unset($_SESSION['access_token']);
            unset($_SESSION['user_id']);
            $message = "Signed out successfully.";
        }
    }

    // Function to create a listing
    if (!function_exists('createListing')) {
        function createListing($postData, $files, $session, $supabaseUrl, $supabaseKey, $storageUrl, $restUrl) {
            global $message;
            // Validate the form token
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
                $fileName = time() . '_' . $imageFile['name'];
                $tmpName = $imageFile['tmp_name'];
                $uploadUrl = $storageUrl . '/object/listings-images/public/' . $fileName;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $uploadUrl);
                curl_setopt($ch, CURLOPT_PUT, true);
                curl_setopt($ch, CURLOPT_INFILE, fopen($tmpName, 'r'));
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

    // Function to fetch listings
    if (!function_exists('fetchListings')) {
        function fetchListings($supabaseKey, $restUrl) {
            global $message, $listings;
            $headers = getCommonHeaders($supabaseKey, null, ['Prefer: return=representation']);
            $response = makeHttpRequest($restUrl . '/listings?order=created_at.desc', 'GET', $headers);
            if ($response === false || (is_array($response) && isset($response['error']))) {
                $message = "Fetch Failed: " . ($response['error']['message'] ?? 'Unknown error');
                return [];
            }
            $listings = $response;
            return $response;
        }
    }

    // Function to search listings
    if (!function_exists('searchListings')) {
        function searchListings($postData, $supabaseKey, $restUrl) {
            global $message, $listings;
            $queryParts = ['order=created_at.desc']; // Start with default ordering
        
            // Animal Type (partial, case-insensitive match)
            if (!empty($postData['animal-type'])) {
                $animalType = trim($postData['animal-type']);
                $queryParts[] = 'type=ilike.' . rawurlencode("%$animalType%");
            }
        
            // Location (partial, case-insensitive match)
            if (!empty($postData['location'])) {
                $location = trim($postData['location']);
                $queryParts[] = 'location=ilike.' . rawurlencode("%$location%");
            }
        
            // Min Price
            if (!empty($postData['min-price'])) {
                $minPrice = filter_var($postData['min-price'], FILTER_VALIDATE_FLOAT);
                if ($minPrice === false || $minPrice < 0) {
                    $message = "Search Failed: Invalid minimum price.";
                    return [];
                }
                $queryParts[] = 'price=gte.' . rawurlencode($minPrice);
            }
        
            // Max Price
            if (!empty($postData['max-price'])) {
                $maxPrice = filter_var($postData['max-price'], FILTER_VALIDATE_FLOAT);
                if ($maxPrice === false || $maxPrice < 0) {
                    $message = "Search Failed: Invalid maximum price.";
                    return [];
                }
                $queryParts[] = 'price=lte.' . rawurlencode($maxPrice);
            }
        
            // Validate price range
            if (isset($minPrice) && isset($maxPrice) && $minPrice > $maxPrice) {
                $message = "Search Failed: Minimum price cannot exceed maximum price.";
                return [];
            }
        
            // Construct the full query
            $query = '?' . implode('&', $queryParts);
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

// Handle POST requests (outside the guard to run every time)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['signup'])) {
        handleSignup($_POST, $supabaseKey, $authUrl);
    } elseif (isset($_POST['signin'])) {
        handleSignin($_POST, $supabaseKey, $authUrl);
    } elseif (isset($_POST['signout'])) {
        handleSignout();
    } elseif (isset($_POST['create_listing'])) {
        createListing($_POST, $_FILES, $_SESSION, $supabaseUrl, $supabaseKey, $storageUrl, $restUrl);
    } elseif (isset($_POST['fetch_listings'])) {
        fetchListings($supabaseKey, $restUrl);
    } elseif (isset($_POST['search'])) {
        searchListings($_POST, $supabaseKey, $restUrl);
    }
}
?>