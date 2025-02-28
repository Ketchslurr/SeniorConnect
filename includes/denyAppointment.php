<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointmentId']) && isset($_POST['denialReason'])) {
    $appointmentId = $_POST['appointmentId'];
    $denialReason = trim($_POST['denialReason']);
    // $status = 
    $sql = "UPDATE appointment SET appointment_status = 'Cancelled', doctor_response = :denialReason WHERE appointmentId = :appointmentId";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute(['denialReason' => $denialReason, 'appointmentId' => $appointmentId])) {
        $_SESSION['success'] = "Appointment denied successfully.";
    } else {
        $_SESSION['error'] = "Failed to deny the appointment.";
    }
}  

header("Location: ../views/HealthcareProfessional/appointments.php");
exit();
?>
