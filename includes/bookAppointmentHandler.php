<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';
session_start();

// Ensure this script only processes POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

// Ensure JSON response format
header("Content-Type: application/json");

// Validate session
if (!isset($_SESSION['userId'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Fetch input values safely
$seniorCitizenId = $_SESSION['seniorId'] ?? null;
$professionalId = $_POST['professionalId'] ?? 0;
$serviceName = trim($_POST['service_name'] ?? '');
$appointmentDate = $_POST['appointment_date'] ?? '';
$appointmentTime = $_POST['appointment_time'] ?? '';

$formatted_time = date("H:i:s", strtotime($appointmentTime));

// Validate required fields
if (!$seniorCitizenId || !$professionalId || empty($appointmentDate) || empty($appointmentTime)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit();
}

// Check if the appointment already exists
$checkSql = "SELECT COUNT(*) FROM appointment WHERE seniorId = ? AND professionalId = ? AND service_name = ? AND appointment_date = ? AND appointment_time = ?";
$checkStmt = $pdo->prepare($checkSql);
$checkStmt->execute([$seniorCitizenId, $professionalId, $serviceName, $appointmentDate, $formatted_time]);
$alreadyBooked = $checkStmt->fetchColumn();

if ($alreadyBooked) {
    echo json_encode(['status' => 'error', 'message' => 'You have already booked this appointment.']);
    exit();
}

// Insert the new appointment
$sql = "INSERT INTO appointment (seniorId, professionalId, service_name, appointment_date, appointment_time) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);

if ($stmt->execute([$seniorCitizenId, $professionalId, $serviceName, $appointmentDate, $formatted_time])) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Appointment booked successfully',
        'script' => '<script>
            document.getElementById("confirmationModal").classList.add("hidden");
            document.getElementById("successModal").classList.remove("hidden");
        </script>'
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again.']);
}
?>
