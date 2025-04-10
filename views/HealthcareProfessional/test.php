<?php
require '../../vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('../../auth/client_secret_573751304329-u46e5l3l4o001omab337gl4e9jbsh8a8.apps.googleusercontent.com.json'); // Path to your downloaded client_secret.json
$client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
$client->setAccessType('offline');
$client->setPrompt('consent');

// Generate the authentication URL
$authUrl = $client->createAuthUrl();
echo "Go to the following URL and grant access:\n$authUrl\n";

// After visiting the URL, Google will give you a code. Enter the code here.
echo "\nEnter verification code: ";
$authCode = trim(fgets(STDIN));

// Exchange the authorization code for an access token and refresh token
$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

// Print the response containing the refresh token
print_r($accessToken);
