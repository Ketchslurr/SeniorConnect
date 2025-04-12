<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

session_start();
include '../../config.php';

if (!isset($_SESSION['userId']) || !isset($_SESSION['professionalId'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['appointmentId'])) {
    echo json_encode(['success' => false, 'error' => 'No appointment ID provided']);
    exit();
}

$appointmentId = $data['appointmentId'];
$professionalId = $_SESSION['professionalId'];

// Fetch appointment details
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

// Generate unique room name using appointment ID and timestamp
$roomName = "SeniorConsult_" . $appointmentId . "_" . time();
$meetingLink = "https://meet.jit.si/" . $roomName;

// Save meeting link in DB
$updateSql = "UPDATE appointment SET meeting_link = :meetingLink WHERE appointmentId = :appointmentId";
$updateStmt = $pdo->prepare($updateSql);
$updateStmt->execute(['meetingLink' => $meetingLink, 'appointmentId' => $appointmentId]);

// Notify senior
$notifSql = "INSERT INTO notifications (seniorId, message, link, created_at) 
             VALUES (:seniorId, :message, :link, NOW())";
$notifStmt = $pdo->prepare($notifSql);
$notifStmt->execute([
    'seniorId' => $appointment['seniorId'],
    'message' => 'Your doctor has scheduled a consultation. Click to join the meeting.',
    'link' => $meetingLink
]);

echo json_encode(['success' => true, 'meet_link' => $meetingLink]);
exit();
