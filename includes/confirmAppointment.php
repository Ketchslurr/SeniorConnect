<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointmentId'])) {
    $appointmentId = $_POST['appointmentId'];

    $sql = "UPDATE appointment SET appointment_status = 'Confirmed' WHERE appointmentId = :appointmentId";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute(['appointmentId' => $appointmentId])) {
        $_SESSION['success'] = "Appointment confirmed successfully.";
    } else {
        $_SESSION['error'] = "Failed to confirm the appointment.";
    }
}

header("Location: ../views/HealthcareProfessional/appointments.php");
exit();
?>
