<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$ref = $_GET['ref'] ?? '';

if (empty($ref)) {
    echo json_encode(['error' => 'Reference number is required']);
    exit();
}

$sql = "SELECT a.appointmentId, a.appointment_date, 
               s.fname AS patientName, h.fname AS doctorName
        FROM appointment a
        JOIN seniorcitizen s ON a.seniorId = s.seniorId
        JOIN healthcareprofessional h ON a.professionalId = h.professionalId
        WHERE a.appointmentId = :ref";

$stmt = $pdo->prepare($sql);
$stmt->execute(['ref' => $ref]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if ($appointment) {
    echo json_encode($appointment);
} else {
    echo json_encode(['error' => 'No appointment found']);
}
?>
