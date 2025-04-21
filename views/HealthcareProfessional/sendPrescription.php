<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['professionalId'])) {
    die("Unauthorized");
}

$prescriptionId = $_POST['prescriptionId'] ?? null;

if (!$prescriptionId) {
    die("Missing prescription ID.");
}

try {
    // Optional: add a `sent_at` timestamp or `status` column in your `prescriptions` table.
    $stmt = $pdo->prepare("UPDATE prescriptions SET status = 'sent', sent_at = NOW() WHERE id = ?");
    $stmt->execute([$prescriptionId]);

    // Optional: notify senior citizen using a `notifications` table
    // You'll need to join appointments to find the senior's ID
    $notifStmt = $pdo->prepare("
        INSERT INTO notifications (user_id, message, created_at)
        SELECT a.seniorId, 'Your prescription has been sent.', NOW()
        FROM prescriptions p
        JOIN appointments a ON p.appointmentId = a.id
        WHERE p.id = ?
    ");
    $notifStmt->execute([$prescriptionId]);

    header("Location: ePrescription.php?sent=1");
    exit();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
