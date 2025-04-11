<?php
require '../vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('../../auth/client_secret.json');
$client->setScopes(Google_Service_Calendar::CALENDAR_EVENTS);
$client->setAccessType('offline');
$client->setPrompt('consent');

// Step 1: Check for the `code` param
if (!isset($_GET['code'])) {
    die('No code provided.');
}

$authCode = $_GET['code'];

// Step 2: Exchange code for access token
$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

// Step 3: Handle error in token exchange
if (isset($accessToken['error'])) {
    die('Error while fetching access token: ' . $accessToken['error']);
}

// Step 4: Set the access token to the client
$client->setAccessToken($accessToken);

// Optional: Save refresh token to DB or file
if (isset($accessToken['refresh_token'])) {
    file_put_contents('../../auth/refresh_token.txt', $accessToken['refresh_token']);
}

// Step 5: Make an authorized API request (test)
$service = new Google_Service_Calendar($client);

$calendarList = $service->calendarList->listCalendarList();

echo "<h3>Calendar List:</h3><ul>";
foreach ($calendarList->getItems() as $calendar) {
    echo "<li>" . htmlspecialchars($calendar->getSummary()) . "</li>";
}
echo "</ul>";
?>
