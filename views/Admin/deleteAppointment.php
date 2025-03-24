<?php
session_start();
if (!isset($_SESSION["adminId"])) {
    header("Location: login.php");
    exit();
}

require '../../config.php';

if (isset($_GET['id'])) {
    $appointmentId = $_GET['id'];

    // Delete appointment
    $stmt = $pdo->prepare("DELETE FROM appointment WHERE appointmentId = :appointmentId");
    $stmt->execute([':appointmentId' => $appointmentId]);

    $_SESSION['success_message'] = "Appointment deleted successfully!";
}

header("Location: manageAppointment.php");
exit();
?>
