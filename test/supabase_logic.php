<?php
session_start(); // Start session to track user authentication

// Supabase credentials (replace with your own)
$supabaseUrl = 'https://owvtdphfvwmvcnstlfnz.supabase.co';
$supabaseKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im93dnRkcGhmdndtdmNuc3RsZm56Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDIyNTUwMjEsImV4cCI6MjA1NzgzMTAyMX0.0ohoAWFipfpWGDKTyPAOlo-IoCJtQTCJEG7ucnWROaE';
$authUrl = $supabaseUrl . '/auth/v1';
$restUrl = $supabaseUrl . '/rest/v1';
$storageUrl = $supabaseUrl . '/storage/v1';

// Handle form submissions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $headers = ['apikey: ' . $supabaseKey, 'Content-Type: application/json'];

    if (isset($_POST['signup'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $data = json_encode(['email' => $email, 'password' => $password]);
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $data
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($authUrl . '/signup', false, $context);
        $response = json_decode($result, true);
        if (isset($response['error'])) {
            $message = "Sign Up Failed: " . $response['error']['message'];
        } else {
            $message = "Sign up successful! Check your email for confirmation.";
        }
    } elseif (isset($_POST['signin'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $data = json_encode(['email' => $email, 'password' => $password]);
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $data
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($authUrl . '/token?grant_type=password', false, $context);
        $response = json_decode($result, true);
        if (isset($response['error'])) {
            $message = "Sign In Failed: " . $response['error']['message'];
        } else {
            $_SESSION['access_token'] = $response['access_token'];
            $message = "Sign in successful!";
        }
    } elseif (isset($_POST['signout'])) {
        unset($_SESSION['access_token']);
        $message = "Signed out successfully.";
    } elseif (isset($_POST['create_listing'])) {
        if (!isset($_SESSION['access_token'])) {
            $message = "Please sign in to create a listing.";
        } else {
            $title = $_POST['title'];
            $price = $_POST['price'];
            $type = $_POST['type'];
            $location = $_POST['location'];
            $imageFile = $_FILES['image'];

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
                    'Authorization: Bearer ' . $_SESSION['access_token'],
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
                    'user_id' => 'user_id_placeholder' // Replace with actual user ID if needed
                ]);
                $options = [
                    'http' => [
                        'method' => 'POST',
                        'header' => implode("\r\n", $headers) . "\r\nAuthorization: Bearer " . $_SESSION['access_token'],
                        'content' => $data
                    ]
                ];
                $context = stream_context_create($options);
                $result = file_get_contents($restUrl . '/listings', false, $context);
                $response = json_decode($result, true);
                if (isset($response['error'])) {
                    $message = "Create Failed: " . $response['error']['message'];
                } else {
                    $message = "Listing created successfully!";
                }
            } else {
                $message = "Image upload failed.";
            }
        }
    } elseif (isset($_POST['fetch_listings'])) {
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers) . "\r\nPrefer: return=representation"
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($restUrl . '/listings?order=created_at.desc', false, $context);
        $listings = json_decode($result, true);
        if (isset($listings['error'])) {
            $message = "Fetch Failed: " . $listings['error']['message'];
        } else {
            $listings = $listings ?? [];
        }
    }
}
?>