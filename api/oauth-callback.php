<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php'; // DB connection

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/client_secret_559478248256-kun11kga64ut761f2hq0jq65o14mqhtb.apps.googleusercontent.com.json');
$client->addScope([
    'https://www.googleapis.com/auth/fitness.heart_rate.read',
    'https://www.googleapis.com/auth/fitness.activity.read',
    'https://www.googleapis.com/auth/fitness.body.read'
]);
$client->setAccessType('offline');
// $client->setRedirectUri('http://localhost/Senior_Connect/api/oauth-callback.php');
$client->setRedirectUri('https://seniorconnect-production.up.railway.app/api/oauth-callback.php');


// Check if code is set
if (!isset($_GET['code'])) {
    exit('Authorization code not provided.');
}

try {
    $client->authenticate($_GET['code']);
    $tokens = $client->getAccessToken();

    if (!$tokens || !isset($tokens['access_token'], $tokens['expires_in'])) {
        throw new Exception('Failed to retrieve valid access token.');
    }

    $refreshToken = $client->getRefreshToken();
    $_SESSION['access_token'] = $tokens;

    // Check for logged-in user
    if (!isset($_SESSION['userId'])) {
        throw new Exception('User session not found.');
    }

    $userId = $_SESSION['userId'];

    // Double check your actual column name (e.g. userId or uid)
    $stmt = $pdo->prepare("UPDATE user_info SET 
        google_fit_access_token = ?, 
        google_fit_refresh_token = ?, 
        google_fit_token_expires = ? 
        WHERE userId = ?"); // Change 'userId' to the correct column in your DB
    $stmt->execute([
        $tokens['access_token'],
        $refreshToken,
        time() + $tokens['expires_in'],
        $userId
    ]);

    header('Location: ../views/SeniorCitizen/realTimeMonitoring.php');
    exit;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// session_start();
// require_once __DIR__ . '/../vendor/autoload.php';
// require_once __DIR__ . '/../config.php'; // Include your DB connection config

// $client = new Google_Client();
// $client->setAuthConfig(__DIR__ . '/client_secret_559478248256-kun11kga64ut761f2hq0jq65o14mqhtb.apps.googleusercontent.com.json');
// $client->addScope([
//     'https://www.googleapis.com/auth/fitness.heart_rate.read',
//     'https://www.googleapis.com/auth/fitness.activity.read',
//     'https://www.googleapis.com/auth/fitness.body.read'
// ]);
// $client->setAccessType('offline');
// $client->setRedirectUri('http://localhost/Senior_Connect/api/oauth-callback.php');

// if (!isset($_GET['code'])) {
//     exit('Authorization code not provided.');
// }

// try {
//     // Exchange code for access token
//     $client->authenticate($_GET['code']);
//     $tokens = $client->getAccessToken();
//     $refreshToken = $client->getRefreshToken();

//     $_SESSION['access_token'] = $tokens;

//     // Validate user session
//     if (!isset($_SESSION['userId'])) {
//         exit('User session not found.');
//     }

//     $userId = $_SESSION['userId'];

//     // Save tokens to DB using $pdo from config.php
//     $stmt = $pdo->prepare("UPDATE user_info SET 
//         google_fit_access_token = ?, 
//         google_fit_refresh_token = ?, 
//         google_fit_token_expires = ? 
//         WHERE userId = ?");
//     $stmt->execute([
//         $tokens['access_token'],
//         $refreshToken,
//         time() + $tokens['expires_in'],
//         $userId
//     ]);

//     // Redirect to dashboard or desired screen
//     header('Location: /googleFit/real-time-heart-rate.php');
//     exit;
// } catch (Exception $e) {
//     echo "Error: " . $e->getMessage();
// }


// session_start();
// require_once __DIR__ . '/../vendor/autoload.php';
// require_once __DIR__ . '/../config.php';

// $client = new Google_Client();
// $client->setAuthConfig(__DIR__ . '/client_secret_559478248256-kun11kga64ut761f2hq0jq65o14mqhtb.apps.googleusercontent.com.json');
// $client->addScope('https://www.googleapis.com/auth/fitness.heart_rate.read');
// $client->addScope('https://www.googleapis.com/auth/fitness.activity.read');
// $client->addScope('https://www.googleapis.com/auth/fitness.body.read');
// $client->setAccessType('offline');
// $client->authenticate($_GET['code']);

// $tokens = $client->getAccessToken();
// $refreshToken = $client->getRefreshToken();
// $userId = $_SESSION['user_id']; // Set this during login

// // Save to DB
// $stmt = $pdo->prepare("UPDATE users SET google_fit_access_token = ?, google_fit_refresh_token = ?, google_fit_token_expires = ? WHERE id = ?");
// $stmt->execute([
//     $tokens['access_token'],
//     $refreshToken,
//     $tokens['expires_in'] + time(),
//     $userId
// ]);

// $_SESSION['access_token'] = $tokens;
// header('Location: /real-time-heart-rate.php');
// exit;
