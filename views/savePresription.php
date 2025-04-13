<?php
include '../../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = $_POST['appointmentId'];
    $prescription = $_POST['prescription'];

    $stmt = $pdo->prepare("INSERT INTO prescriptions (appointmentId, prescription_text, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$appointmentId, $prescription]);

    header("Location: ../appointments.php?message=Prescription+saved");
    exit();
}
?>
