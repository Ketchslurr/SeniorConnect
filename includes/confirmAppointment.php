<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointmentId']) && isset($_POST['paymentId'])) {
    $appointmentId = $_POST['appointmentId'];
    $paymentId = $_POST['paymentId'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Update appointment status
        $sql1 = "UPDATE appointment SET appointment_status = 'Confirmed' WHERE appointmentId = :appointmentId";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute(['appointmentId' => $appointmentId]);

        // Update payment status to Verified
        $sql2 = "UPDATE payments SET status = 'Verified' WHERE paymentId = :paymentId";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute(['paymentId' => $paymentId]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['success'] = "Appointment confirmed and payment status updated successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Failed to confirm appointment: " . $e->getMessage();
    }
}

header("Location: ../views/HealthcareProfessional/appointments.php");
exit();
?>
