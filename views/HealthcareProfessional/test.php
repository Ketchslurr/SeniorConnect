<?php
require '../../vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('../../auth/client_secret.json'); // Path to your downloaded client_secret.json
$client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
$client->addScope('email');
$client->addScope('profile');
$client->addScope('https://www.googleapis.com/auth/calendar.events');
// $client->setAccessType('offline');
// $client->setPrompt('consent');

// Step 1: If the code is not present, redirect to the Google authorization page
if (!isset($_GET['code'])) {
    
    // Generate the authorization URL
    $authUrl = $client->createAuthUrl();
    // Redirect the user to the authorization URL
    header('Location: ' . $authUrl);
    
    if (isset($token['error'])) {
        throw new Exception("Token error: " . json_encode($token));
    }
    echo "<pre>"; print_r($token); echo "</pre>"; exit();

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
