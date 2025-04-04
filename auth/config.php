<?php
require '../vendor/autoload.php';

session_start();

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/client_secret_573751304329-u46e5l3l4o001omab337gl4e9jbsh8a8.apps.googleusercontent.com.json');
$client->setRedirectUri('https://senior-production-f9d8.up.railway.app/auth/oauth-callback.php');
$client->addScope(Google\Service\Calendar::CALENDAR);
$client->setAccessType('offline'); // To get a refresh token
$client->setPrompt('consent');

// Store the Google client in session
$_SESSION['google_client'] = serialize($client);
