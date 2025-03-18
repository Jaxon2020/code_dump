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

// Function to handle listing creation
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
            'user_id' => 'user_id_placeholder'
        ]);
        $headers = ['apikey: ' . $supabaseKey, 'Content-Type: application/json', 'Authorization: Bearer ' . $session['access_token']];
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $data
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($restUrl . '/listings', false, $context);
        $response = json_decode($result, true);
        $message = isset($response['error']) ? "Create Failed: " . $response['error']['message'] : "Listing created successfully!";
    } else {
        $message = "Image upload failed.";
    }
}

// Function to fetch listings
function fetchListings($supabaseKey, $restUrl) {
    global $message, $listings;
    $headers = ['apikey: ' . $supabaseKey, 'Content-Type: application/json', 'Prefer: return=representation'];
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($restUrl . '/listings?order=created_at.desc', false, $context);
    $listings = json_decode($result, true);
    if (isset($listings['error'])) {
        $message = "Fetch Failed: " . $listings['error']['message'];
        $listings = [];
    }
}

// Function to search listings (basic example, expand as needed)
function searchListings($postData, $supabaseKey, $restUrl) {
    global $message, $listings;
    $query = '';
    if (!empty($postData['animal-type'])) $query .= '&type=eq.' . urlencode($postData['animal-type']);
    if (!empty($postData['location'])) $query .= '&location=eq.' . urlencode($postData['location']);
    if (!empty($postData['min-price'])) $query .= '&price=gte.' . urlencode($postData['min-price']);
    if (!empty($postData['max-price'])) $query .= '&price=lte.' . urlencode($postData['max-price']);

    $headers = ['apikey: ' . $supabaseKey, 'Content-Type: application/json', 'Prefer: return=representation'];
    $options = [
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers)
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($restUrl . '/listings?order=created_at.desc' . $query, false, $context);
    $listings = json_decode($result, true);
    if (isset($listings['error'])) {
        $message = "Search Failed: " . $listings['error']['message'];
        $listings = [];
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_listing'])) {
        createListing($_POST, $_FILES, $_SESSION, $supabaseUrl, $supabaseKey, $storageUrl, $restUrl);
    } elseif (isset($_POST['fetch_listings'])) {
        fetchListings($supabaseKey, $restUrl);
    } elseif (isset($_POST['search'])) {
        searchListings($_POST, $supabaseKey, $restUrl);
    }
}
?>