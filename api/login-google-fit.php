<?php
$client_id = '559478248256-kun11kga64ut761f2hq0jq65o14mqhtb.apps.googleusercontent.com';
// $redirect_uri = 'http://localhost/Senior_Connect/api/oauth-callback.php';
$redirect_uri = 'https://seniorconnect-production.up.railway.app/api/oauth-callback.php';

$scope = 'https://www.googleapis.com/auth/fitness.heart_rate.read https://www.googleapis.com/auth/fitness.activity.read https://www.googleapis.com/auth/fitness.body.read';

$auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'response_type' => 'code',
    'scope' => $scope,
    'access_type' => 'offline',
    'prompt' => 'consent'
]);

header("Location: $auth_url");
exit;
