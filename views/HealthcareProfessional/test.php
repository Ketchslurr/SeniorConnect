<?php
require '../../vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('../../auth/client_secret_573751304329-u46e5l3l4o001omab337gl4e9jbsh8a8.apps.googleusercontent.com.json'); // Path to your downloaded client_secret.json
$client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
$client->setAccessType('offline');
$client->setPrompt('consent');

// Step 1: If the code is not present, redirect to the Google authorization page
if (!isset($_GET['code'])) {
    // Generate the authorization URL
    $authUrl = $client->createAuthUrl();
    // Redirect the user to the authorization URL
    header('Location: ' . $authUrl);
    exit();
}

// Step 2: If the code is present, exchange it for the refresh token
$authCode = $_GET['code'];
$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

// Store the access token and refresh token
if (isset($accessToken['refresh_token'])) {
    echo 'Refresh Token: ' . $accessToken['refresh_token']; // Output the refresh token to copy it
} else {
    echo 'Error: No refresh token found.';
}
?>
