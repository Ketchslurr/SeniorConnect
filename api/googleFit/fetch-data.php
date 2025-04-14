<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config.php'; 
header('Content-Type: application/json');


if (!isset($_SESSION['access_token'])) {
    // header("Location: ../login-google-fit.php");
    // exit;
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['userId']; // ensure user is logged in

// $pdo = new PDO($dsn, $username, $password);
$stmt = $pdo->prepare("SELECT google_fit_access_token, google_fit_refresh_token, google_fit_token_expires FROM user_info WHERE userId = ?");
$stmt->execute([$userId]);
$tokens = $stmt->fetch(PDO::FETCH_ASSOC);

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/../client_secret_559478248256-kun11kga64ut761f2hq0jq65o14mqhtb.apps.googleusercontent.com.json');
$client->addScope('https://www.googleapis.com/auth/fitness.heart_rate.read');
$client->setAccessType('offline');

// Set token manually
$client->setAccessToken([
    'access_token' => $tokens['google_fit_access_token'],
    'expires_in' => $tokens['google_fit_token_expires'] - time(),
    'refresh_token' => $tokens['google_fit_refresh_token'],
    'created' => time()
]);

// Refresh if expired
if ($client->isAccessTokenExpired()) {
    $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    $client->setAccessToken($newToken);

    $stmt = $pdo->prepare("UPDATE user_info SET 
        google_fit_access_token = ?, 
        google_fit_token_expires = ? 
        WHERE userId = ?");
    $stmt->execute([
        $newToken['access_token'],
        time() + $newToken['expires_in'],
        $userId
    ]);
}

$now = round(microtime(true) * 1000);
$tenMinsAgo = $now - (10 * 60 * 1000);

$body = [
    "aggregateBy" => [
        ["dataTypeName" => "com.google.heart_rate.bpm"],
        ["dataTypeName" => "com.google.step_count.delta"],
        ["dataTypeName" => "com.google.calories.expended"],
    ],
    // "bucketByTime" => ["durationMillis" => 60000], // 1 minute
    // "startTimeMillis" => intval($tenMinsAgo / 1000000),
    // "endTimeMillis" => intval($now / 1000000)
    "bucketByTime" => ["durationMillis" => 60000], // 1 minute
    "startTimeMillis" => $tenMinsAgo,
    "endTimeMillis" => $now
];

$http = new \GuzzleHttp\Client();
$url = 'https://www.googleapis.com/fitness/v1/users/me/dataset:aggregate';

$response = $http->post($url, [
    'headers' => [
        'Authorization' => 'Bearer ' . $client->getAccessToken()['access_token'],
        'Content-Type' => 'application/json'
    ],
    'json' => $body
]);

$data = json_decode($response->getBody(), true);
$results = [];

// foreach ($data['bucket'] as $bucket) {
//     $time = intval($bucket['startTimeMillis']);
//     $entry = ['time' => $time];

//     foreach ($bucket['dataset'] as $dataset) {
//         $type = $dataset['dataSourceId'] ?? '';
//         if (!empty($dataset['point'])) {
//             $point = $dataset['point'][0];
//             $value = $point['value'][0];
//             switch ($point['dataTypeName']) {
//                 case 'com.google.heart_rate.bpm':
//                     $entry['bpm'] = $value['fpVal'];
//                     break;
//                 case 'com.google.step_count.delta':
//                     $entry['steps'] = $value['intVal'];
//                     break;
//                 case 'com.google.calories.expended':
//                     $entry['calories'] = $value['fpVal'];
//                     break;
//             }
//         }
//     }
//     $results[] = $entry;
// }

foreach ($data['bucket'] as $bucket) {
    $time = intval($bucket['startTimeMillis']);
    $entry = ['time' => $time]; // initialize empty entry

    foreach ($bucket['dataset'] as $dataset) {
        if (!empty($dataset['point'])) {
            foreach ($dataset['point'] as $point) {
                $dataType = $point['dataTypeName'];
                $value = $point['value'][0] ?? null;

                if (!$value) continue;

                switch ($dataType) {
                    case 'com.google.heart_rate.bpm':
                        $entry['bpm'] = $value['fpVal'] ?? null;
                        break;
                    case 'com.google.step_count.delta':
                        $entry['steps'] = $value['intVal'] ?? 0;
                        break;
                    case 'com.google.calories.expended':
                        $entry['calories'] = $value['fpVal'] ?? 0.0;
                        break;
                }
            }
        }
    }

    // only include entry if at least one metric is present
    if (isset($entry['bpm']) || isset($entry['steps']) || isset($entry['calories'])) {
        $results[] = $entry;
    }
}

echo json_encode($results);