<!-- <//?php
// session_start();
// if (!isset($_SESSION['google_fit_access_token'])) {
//     die("Not authorized.");
// }

// $access_token = $_SESSION['google_fit_access_token'];
// $dataset = time() * 1000000;
// $start = strtotime('-1 hour') * 1000000000;
// $end = time() * 1000000000;

// $url = "https://www.googleapis.com/fitness/v1/users/me/dataset:aggregate";

// $headers = [
//     "Authorization: Bearer $access_token",
//     "Content-Type: application/json"
// ];

// $body = json_encode([
//     "aggregateBy" => [[
//         "dataTypeName" => "com.google.heart_rate.bpm"
//     ]],
//     "bucketByTime" => [ "durationMillis" => 3600000 ],
//     "startTimeMillis" => $start / 1000000,
//     "endTimeMillis" => $end / 1000000
// ]);

// $ch = curl_init($url);
// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

// $response = curl_exec($ch);
// $data = json_decode($response, true);

// echo "<pre>";
// print_r($data);
// echo "</pre>";
?>

<script>
setInterval(() => {
    fetch("/api/googleFit/fetch-data.php")
        .then(res => res.json())
        .then(data => {
            // process and display data
            console.log("Live Heart Rate Data:", data);
        });
}, 10000); // Fetch every 10s
</script> -->

<?php
// session_start();

// require_once __DIR__ . '/vendor/autoload.php'; // Adjust path if needed

// $client = new Google_Client();
// $client->setAuthConfig(__DIR__ . '/client_secret_559478248256-kun11kga64ut761f2hq0jq65o14mqhtb.apps.googleusercontent.com.json');
// $client->addScope('https://www.googleapis.com/auth/fitness.heart_rate.read');
// $client->setAccessType('offline');

// if (!isset($_SESSION['access_token'])) {
//     http_response_code(401);
//     echo json_encode(['error' => 'User not authenticated']);
//     exit;
// }

// $client->setAccessToken($_SESSION['access_token']);

// // Handle token refresh
// if ($client->isAccessTokenExpired()) {
//     $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
//     $_SESSION['access_token'] = $client->getAccessToken();
// }

// // Convert time to nanoseconds
// $now = round(microtime(true) * 1000000000);
// $tenMinsAgo = $now - (10 * 60 * 1000000000);

// // Google Fit Data Source
// $dataset = "{$tenMinsAgo}-{$now}";
// $url = "https://www.googleapis.com/fitness/v1/users/me/dataset:aggregate";

// $body = [
//     "aggregateBy" => [[
//         "dataTypeName" => "com.google.heart_rate.bpm",
//     ]],
//     "bucketByTime" => ["durationMillis" => 60000],
//     "startTimeMillis" => intval($tenMinsAgo / 1000000),
//     "endTimeMillis" => intval($now / 1000000)
// ];

// $http = new GuzzleHttp\Client();
// $response = $http->post($url, [
//     'headers' => [
//         'Authorization' => 'Bearer ' . $client->getAccessToken()['access_token'],
//         'Content-Type' => 'application/json'
//     ],
//     'json' => $body
// ]);

// $data = json_decode($response->getBody(), true);
// $heartRates = [];

// foreach ($data['bucket'] as $bucket) {
//     if (!empty($bucket['dataset'][0]['point'])) {
//         foreach ($bucket['dataset'][0]['point'] as $point) {
//             $time = intval($point['startTimeNanos']) / 1000000;
//             $bpm = $point['value'][0]['fpVal'];
//             $heartRates[] = ['time' => $time, 'bpm' => $bpm];
//         }
//     }
// }

// echo json_encode($heartRates);

session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php'; 

if (!isset($_SESSION['access_token'])) {
    header("Location: /api/login-google-fit.php");
    exit;
}

$userId = $_SESSION['user_id']; // ensure user is logged in

$pdo = new PDO($dsn, $username, $password);
$stmt = $pdo->prepare("SELECT google_fit_access_token, google_fit_refresh_token, google_fit_token_expires FROM user_info WHERE id = ?");
$stmt->execute([$userId]);
$tokens = $stmt->fetch(PDO::FETCH_ASSOC);

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/client_secret_559478248256-kun11kga64ut761f2hq0jq65o14mqhtb.apps.googleusercontent.com.json');
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

    $stmt = $pdo->prepare("UPDATE users SET 
        google_fit_access_token = ?, 
        google_fit_token_expires = ? 
        WHERE id = ?");
    $stmt->execute([
        $newToken['access_token'],
        time() + $newToken['expires_in'],
        $userId
    ]);
}

$body = [
    "aggregateBy" => [
        ["dataTypeName" => "com.google.heart_rate.bpm"],
        ["dataTypeName" => "com.google.step_count.delta"],
        ["dataTypeName" => "com.google.calories.expended"],
    ],
    "bucketByTime" => ["durationMillis" => 60000], // 1 minute
    "startTimeMillis" => intval($tenMinsAgo / 1000000),
    "endTimeMillis" => intval($now / 1000000)
];

$response = $http->post($url, [
    'headers' => [
        'Authorization' => 'Bearer ' . $client->getAccessToken()['access_token'],
        'Content-Type' => 'application/json'
    ],
    'json' => $body
]);

$data = json_decode($response->getBody(), true);
$results = [];

foreach ($data['bucket'] as $bucket) {
    $time = intval($bucket['startTimeMillis']);
    $entry = ['time' => $time];

    foreach ($bucket['dataset'] as $dataset) {
        $type = $dataset['dataSourceId'] ?? '';
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
    $results[] = $entry;
}

echo json_encode($results);