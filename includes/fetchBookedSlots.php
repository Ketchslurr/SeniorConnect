<?php
include '../config.php';

$professionalId = isset($_GET['professionalId']) ? intval($_GET['professionalId']) : 0;
$appointmentDate = isset($_GET['date']) ? $_GET['date'] : '';

if ($professionalId === 0 || empty($appointmentDate)) {
    echo json_encode([]);
    exit();
}

// Fetch booked appointment times
$sql = "SELECT appointment_time FROM appointment WHERE professionalId = :professionalId AND appointment_date = :appointmentDate";
$stmt = $pdo->prepare($sql);
$stmt->execute(['professionalId' => $professionalId, 'appointmentDate' => $appointmentDate]);
$bookedSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Convert time to a consistent format (remove seconds)
$bookedSlots = array_map(function($time) {
    return date("g:i A", strtotime($time)); // Convert to 12-hour format (e.g., "9:00 AM")
}, $bookedSlots);

header('Content-Type: application/json');
echo json_encode($bookedSlots ?: []);
exit();
