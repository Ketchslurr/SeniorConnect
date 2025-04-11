<?php
require '../vendor/autoload.php';

session_start();

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/client_secret_573751304329-4hia4bhoqa6vt3uk8of75ps7eqep5tka.apps.googleusercontent.com.json');
$client->setRedirectUri('https://senior-production-f9d8.up.railway.app/auth/oauth-callback.php');
$client->addScope(Google\Service\Calendar::CALENDAR);
$client->setAccessType('offline'); // To get a refresh token
$client->setPrompt('consent');

// Store the Google client in session
$_SESSION['google_client'] = serialize($client);
