<?php
session_start();
if (!isset($_SESSION["adminId"])) {
    header("Location: login.php");
    exit();
}

require '../../config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['appointmentId'], $_POST['action'])) {
    $appointmentId = $_POST['appointmentId'];
    $action = $_POST['action'];
    
    // If the admin accepts, prompt for meeting link
    if ($action === "accept") {
        $meeting_link = $_POST['meeting_link'] ?? null;
        $stmt = $pdo->prepare("UPDATE appointment SET appointment_status = 'Confirmed', meeting_link = :meeting_link WHERE appointmentId = :appointmentId");
        $stmt->execute([':meeting_link' => $meeting_link, ':appointmentId' => $appointmentId]);
    } else {
        $stmt = $pdo->prepare("UPDATE appointment SET appointment_status = 'Cancelled', meeting_link = NULL WHERE appointmentId = :appointmentId");
        $stmt->execute([':appointmentId' => $appointmentId]);
    }

    header("Location: manageAppointment.php");
    exit();
} else {
    header("Location: manageAppointment.php");
    exit();
}
?>
