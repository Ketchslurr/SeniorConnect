<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['professionalId'])) {
    die("Unauthorized");
}

$prescriptionId = $_POST['prescriptionId'] ?? null;
$prescription = $_POST['prescription_text'] ?? null;
$instructions = $_POST['instructions'] ?? null;

if (!$prescriptionId || !$prescription || !$instructions) {
    die("Missing required fields.");
}

try {
    $stmt = $pdo->prepare("UPDATE prescriptions SET prescription_text = ?, instructions = ? WHERE id = ?");
    $stmt->execute([$prescription, $instructions, $prescriptionId]);

    header("Location: ePrescription.php?edited=1");
    exit();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
