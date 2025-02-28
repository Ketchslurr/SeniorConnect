<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$seniorCitizenId = $_SESSION['userId']; // Get the logged-in senior citizen's ID

$sql = "SELECT appointmentId, service_name, appointment_date, appointment_time 
        FROM appointment 
        WHERE seniorId = ? 
        ORDER BY appointment_date, appointment_time";

$stmt = $pdo->prepare($sql);
$stmt->execute([$seniorCitizenId]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = [];

foreach ($appointments as $appointment) {
    $events[] = [
        'id' => $appointment['appointmentId'],
        'title' => $appointment['service_name'],
        'start' => $appointment['appointment_date'] . 'T' . $appointment['appointment_time'],
    ];
}

echo json_encode($events);
?>
