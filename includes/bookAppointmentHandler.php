<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$seniorCitizenId = $_SESSION['seniorId'];  // Assuming userId is the senior citizen's ID
$professionalId = isset($_POST['professionalId']) ? intval($_POST['professionalId']) : 0;
$serviceName = isset($_POST['service_name']) ? trim($_POST['service_name']) : '';
$appointmentDate = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : '';
$appointmentTime = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : '';

if ($professionalId === 0 || empty($serviceName) || empty($appointmentDate) || empty($appointmentTime)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit();
}

// Check if the same senior has already booked this time slot for the same service and doctor
$checkSql = "SELECT COUNT(*) FROM appointment WHERE seniorId = ? AND professionalId = ? AND service_name = ? AND appointment_date = ? AND appointment_time = ?";
$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute([$seniorCitizenId, $professionalId, $serviceName, $appointmentDate, $appointmentTime]);
$alreadyBooked = $checkStmt->fetchColumn();

if ($alreadyBooked) {
    echo json_encode(['status' => 'error', 'message' => 'You have already booked this appointment.']);
    exit();
}
// // Check if the selected time slot is already booked
// $checkSql = "SELECT COUNT(*) FROM appointment WHERE professionalId = ? AND appointment_date = ? AND appointment_time = ?";
// $checkStmt = $pdo->prepare($checkSql);
// $checkStmt->execute([$professionalId, $appointmentDate, $appointmentTime]);
// $alreadyBooked = $checkStmt->fetchColumn();

// if ($alreadyBooked) {
//     echo json_encode(['status' => 'error', 'message' => 'This time slot is already booked.']);
//     exit();
// }

// Insert appointment
$sql = "INSERT INTO appointment (seniorId, professionalId, service_name, appointment_date, appointment_time) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$seniorCitizenId, $professionalId, $serviceName, $appointmentDate, $appointmentTime])) {
    echo json_encode(['status' => 'success', 'message' => 'Appointment booked successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again.']);
}
?>
