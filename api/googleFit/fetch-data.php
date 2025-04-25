<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config.php'; 
header('Content-Type: application/json');

// Check auth
if (!isset($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['userId']; // User must be logged in

// Get tokens from DB
$stmt = $pdo->prepare("SELECT google_fit_access_token, google_fit_refresh_token, google_fit_token_expires FROM user_info WHERE userId = ?");
$stmt->execute([$userId]);
$tokens = $stmt->fetch(PDO::FETCH_ASSOC);

// Init Google Client
$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/../client_secret_559478248256-kun11kga64ut761f2hq0jq65o14mqhtb.apps.googleusercontent.com.json');
$client->addScope('https://www.googleapis.com/auth/fitness.heart_rate.read');
$client->addScope('https://www.googleapis.com/auth/fitness.activity.read');
$client->addScope('https://www.googleapis.com/auth/fitness.body.read');
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

    // Update DB
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

// 🔍 Filter support: ?filter=10min | 1h | 24h | 7d | 30d
$filter = $_GET['filter'] ?? '10min';
switch ($filter) {
    case '1h':
        $durationMillis = 60 * 60 * 1000;
        break;
    case '24h':
        $durationMillis = 24 * 60 * 60 * 1000;
        break;
    case '7d':
        $durationMillis = 7 * 24 * 60 * 60 * 1000;
        break;
    case '30d':
        $durationMillis = 30 * 24 * 60 * 60 * 1000;
        break;
    case '10min':
    default:
        $durationMillis = 10 * 60 * 1000;
        break;
}

$now = round(microtime(true) * 1000); // current in ms
$startTime = $now - $durationMillis;

// Adjust bucket duration for larger timeframes
if ($durationMillis > 24 * 60 * 60 * 1000) {
    $bucketDuration = 24 * 60 * 60 * 1000; // group by day
} elseif ($durationMillis > 60 * 60 * 1000) {
    $bucketDuration = 60 * 60 * 1000; // group by hour
} else {
    $bucketDuration = 60 * 1000; // group by minute
}

// Build request body
$body = [
   "aggregateBy" => [
        [
            "dataTypeName" => "com.google.heart_rate.bpm",
            // "dataSourceId" => "derived:com.google.heart_rate.bpm:com.google.android.gms:merge_heart_rate_bpm"
        ],
        ["dataTypeName" => "com.google.step_count.delta"],
        ["dataTypeName" => "com.google.calories.expended"],
    ],
    "bucketByTime" => ["durationMillis" => $bucketDuration],
    "startTimeMillis" => $startTime,
    "endTimeMillis" => $now
];

// Call API
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
// file_put_contents('fit_debug.json', json_encode($data, JSON_PRETTY_PRINT));
file_put_contents(__DIR__ . '/fit_debug.json', json_encode($data, JSON_PRETTY_PRINT));
$results = [];

foreach ($data['bucket'] as $bucket) {
    $time = intval($bucket['startTimeMillis']);
    $entry = ['time' => $time];

    foreach ($bucket['dataset'] as $dataset) {
        if (!empty($dataset['point'])) {
            $point = $dataset['point'][0];
            $value = $point['value'][0];
            switch ($point['dataTypeName']) {
                case 'com.google.heart_rate.bpm':
                    $entry['bpm'] = $value['fpVal'];
                    break;
                case 'com.google.step_count.delta':
                    $entry['steps'] = $value['intVal'];
                    break;
                case 'com.google.calories.expended':
                    $entry['calories'] = $value['fpVal'];
                    break;
            }
        }
    }

    if (count($entry) > 1) {
        $results[] = $entry;
    }
}

// Add fake data if empty
if (empty($results)) {
    $interval = $bucketDuration;
    $count = intval($durationMillis / $interval);

    for ($i = $count - 1; $i >= 0; $i--) {
        $timestamp = $now - ($i * $interval);
        $results[] = [
            'time' => $timestamp,
            'bpm' => rand(60, 100),
            'steps' => rand(0, 15),
            'calories' => round(rand(1, 5) + (rand(0, 9) / 10), 1),
            'fake' => true
        ];
    }
}

echo json_encode($results);
