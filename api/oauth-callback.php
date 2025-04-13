<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php'; // adjust if your DB connection is named differently

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/client_secret_559478248256-kun11kga64ut761f2hq0jq65o14mqhtb.apps.googleusercontent.com.json');
$client->addScope([
    'https://www.googleapis.com/auth/fitness.heart_rate.read',
    'https://www.googleapis.com/auth/fitness.activity.read',
    'https://www.googleapis.com/auth/fitness.body.read'
]);
$client->setAccessType('offline');
$client->setRedirectUri('http://localhost/Senior_Connect/api/oauth-callback.php');

// Exchange code for token
$client->authenticate($_GET['code']);
$tokens = $client->getAccessToken();
$refreshToken = $client->getRefreshToken();

$_SESSION['access_token'] = $tokens;

// Save tokens to DB
$userId = $_SESSION['user_id']; // Make sure this was set before login flow

try {
    $pdo = new PDO($dsn, $username, $password); // Or use your db.php config

    $stmt = $pdo->prepare("UPDATE users SET 
        google_fit_access_token = ?, 
        google_fit_refresh_token = ?, 
        google_fit_token_expires = ? 
        WHERE id = ?");
    $stmt->execute([
        $tokens['access_token'],
        $refreshToken,
        time() + $tokens['expires_in'],
        $userId
    ]);

    header('Location: /googleFit/real-time-heart-rate.php');
    exit;
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
}

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
