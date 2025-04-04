<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require __DIR__ . '../../../vendor/autoload.php'; // Google API Client

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;

session_start();
include '../../config.php';

if (!isset($_SESSION['userId']) || !isset($_SESSION['professionalId'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['appointmentId'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request. No appointment ID.']);
    exit();
}

$appointmentId = $data['appointmentId'];
$professionalId = $_SESSION['professionalId'];

// 🔹 Load Google API Credentials
$client = new Client();
// $client->setAuthConfig(__DIR__ . '/../../auth/client_secret.json');
$client->setAuthConfig('../../api/client_secret.json'); // Ensure this path is correct
$client->setScopes(Calendar::CALENDAR_EVENTS);
$client->setAccessType('offline');

// 🔹 OAuth 2.0 Authentication using Refresh Token
$refreshToken = '1//04ZTV_-RXzZqjCgYIARAAGAQSNwF-L9IrnRGeQsF87V7mFqyc8ow32aNmuYObtNrcgtenIb9HuKDgFqgPRLFpD20ADW4pt6ixX9I'; // Replace with your refresh token

if (!$refreshToken) {
    die("ERROR: Refresh token is missing!");
}

$accessToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);

// Log the error response from Google
if (isset($accessToken['error'])) {
    error_log('Google OAuth Error: ' . print_r($accessToken, true));
    echo json_encode(['success' => false, 'error' => 'Google OAuth error: ' . $accessToken['error_description']]);
    exit();
}

$client->setAccessToken($accessToken);
$client->fetchAccessTokenWithRefreshToken($refreshToken);
$accessToken = $client->getAccessToken();
$client->setAccessToken($accessToken);

$calendarService = new Calendar($client);
$calendarId = 'primary';

// 🔹 Fetch appointment details
$sql = "SELECT ha.*, sc.*
        FROM appointment ha
        JOIN seniorcitizen sc ON ha.seniorId = sc.seniorId
        WHERE ha.appointmentId = :appointmentId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['appointmentId' => $appointmentId]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    echo json_encode(['success' => false, 'error' => 'Appointment not found.']);
    exit();
}

// 🔹 Create Google Meet Event
$event = new Google_Service_Calendar_Event([
    'summary' => 'Medical Consultation with ' . $appointment['fName'],
    'start' => [
        'dateTime' => $appointment['appointment_date'] . 'T09:00:00',
        'timeZone' => 'Asia/Manila',
    ],
    'end' => [
        'dateTime' => $appointment['appointment_date'] . 'T09:30:00',
        'timeZone' => 'Asia/Manila',
    ],
    'conferenceData' => [
        'createRequest' => [
            'requestId' => uniqid(), // Unique identifier
            'conferenceSolutionKey' => [
                'type' => 'hangoutsMeet' 
            ],
        ]
    ],
    'attendees' => [
        ['email' => $appointment['seniorEmail']], // Senior's email
        ['email' => $_SESSION['doctorEmail']] // Doctor's email
    ]
]);

$event = $calendarService->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);

$meetingLink = $event->getHangoutLink();

// 🔹 Update database with the meeting link
$updateSql = "UPDATE appointment SET meeting_link = :meetingLink WHERE appointmentId = :appointmentId";
$updateStmt = $pdo->prepare($updateSql);
$updateStmt->execute(['meetingLink' => $meetingLink, 'appointmentId' => $appointmentId]);

$notifSql = "INSERT INTO notifications (seniorId, message, link, created_at) 
             VALUES (:seniorId, :message, :link, NOW())";
$notifStmt = $pdo->prepare($notifSql);
$notifStmt->execute([
    'seniorId' => $appointment['seniorId'],
    'message' => 'Your doctor has scheduled a consultation. Click to join the meeting.',
    'link' => $meetingLink
]);

// 🔹 Return the meeting link as JSON
echo json_encode(['success' => true, 'meet_link' => $meetingLink]);
exit();
