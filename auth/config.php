<?php
require '../vendor/autoload.php';

session_start();

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/client_secret.json');
// $client->setRedirectUri('https://senior-production-f9d8.up.railway.app/auth/oauth-callback.php');
$client->setRedirectUri('http://localhost/Senior_Connect/auth/oauth-callback.php');
$client->addScope(Google\Service\Calendar::CALENDAR);
$client->setAccessType('offline'); // To get a refresh token
$client->setPrompt('consent');

// Store the Google client in session
$_SESSION['google_client'] = serialize($client);
