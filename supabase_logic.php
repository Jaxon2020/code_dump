<?php
session_start(); // Start session to track user authentication

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

// Helper function for common headers
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

// Function to handle signup
function handleSignup($postData, $supabaseKey, $authUrl) {
    global $message;
    $email = $postData['email'];
    $password = $postData['password'];
    $data = json_encode(['email' => $email, 'password' => $password]);
    $headers = getCommonHeaders($supabaseKey);
    $response = makeHttpRequest($authUrl . '/signup', 'POST', $headers, $data);
    if ($response && isset($response['error'])) {
        $message = "Sign Up Failed: " . $response['error']['message'];
    } else {
        $message = "Sign up successful! Check your email for confirmation.";
    }
}

// Function to handle signin
function handleSignin($postData, $supabaseKey, $authUrl) {
    global $message;
    $email = $postData['email'];
    $password = $postData['password'];
    $data = json_encode(['email' => $email, 'password' => $password]);
    $headers = getCommonHeaders($supabaseKey);
    $response = makeHttpRequest($authUrl . '/token?grant_type=password', 'POST', $headers, $data);
    if ($response && isset($response['error'])) {
        $message = "Sign In Failed: " . $response['error']['message'];
    } else if ($response) {
        $_SESSION['access_token'] = $response['access_token'];
        $_SESSION['user_id'] = $response['user']['id']; // Store user ID for later use
        $message = "Sign in successful!";
    } else {
        $message = "Sign In Failed: Unknown error";
    }
}

// Function to handle signout
function handleSignout() {
    global $message;
    unset($_SESSION['access_token']);
    unset($_SESSION['user_id']);
    $message = "Signed out successfully.";
}

// Existing listing functions (unchanged for brevity)
function createListing($postData, $files, $session, $supabaseUrl, $supabaseKey, $storageUrl, $restUrl) {
    global $message;
    if (!isset($session['access_token'])) {
        $message = "Please sign in to create a listing.";
        return;
    }
    $title = $postData['title'];
    $price = $postData['price'];
    $type = $postData['type'];
    $location = $postData['location'];
    $imageFile = $files['image'];
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
        curl_exec($ch);
        curl_close($ch);
        $imageUrl = $supabaseUrl . '/storage/v1/object/public/listings-images/public/' . $fileName;
        $data = json_encode([
            'title' => $title,
            'price' => $price,
            'type' => $type,
            'location' => $location,
            'image_url' => $imageUrl,
            'user_id' => $session['user_id'] // Use actual user ID from session
        ]);
        $headers = getCommonHeaders($supabaseKey, $session['access_token']);
        $response = makeHttpRequest($restUrl . '/listings', 'POST', $headers, $data);
        $message = $response && isset($response['error']) ? "Create Failed: " . $response['error']['message'] : "Listing created successfully!";
    } else {
        $message = "Image upload failed.";
    }
}

function fetchListings($supabaseKey, $restUrl) {
    global $message, $listings;
    $headers = getCommonHeaders($supabaseKey, null, ['Prefer: return=representation']);
    $response = makeHttpRequest($restUrl . '/listings?order=created_at.desc', 'GET', $headers);
    if ($response === false || isset($response['error'])) {
        $message = "Fetch Failed: " . ($response['error']['message'] ?? 'Unknown error');
        $listings = [];
    } else {
        $listings = $response;
    }
}

function searchListings($postData, $supabaseKey, $restUrl) {
    global $message, $listings;
    $query = '';
    if (!empty($postData['animal-type'])) $query .= '&type=eq.' . urlencode($postData['animal-type']);
    if (!empty($postData['location'])) $query .= '&location=eq.' . urlencode($postData['location']);
    if (!empty($postData['min-price'])) $query .= '&price=gte.' . urlencode($postData['min-price']);
    if (!empty($postData['max-price'])) $query .= '&price=lte.' . urlencode($postData['max-price']);
    $headers = getCommonHeaders($supabaseKey, null, ['Prefer: return=representation']);
    $response = makeHttpRequest($restUrl . '/listings?order=created_at.desc' . $query, 'GET', $headers);
    if ($response === false || isset($response['error'])) {
        $message = "Search Failed: " . ($response['error']['message'] ?? 'Unknown error');
        $listings = [];
    } else {
        $listings = $response;
    }
}

// Handle POST requests
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