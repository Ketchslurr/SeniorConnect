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
    $stmt = $pdo->prepare("UPDATE prescriptions SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$prescriptionId]);

    header("Location: ePrescription.php?deleted=1");
    exit();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
