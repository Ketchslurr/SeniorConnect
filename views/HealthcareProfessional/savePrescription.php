<!-- <//?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointmentId = $_POST['appointmentId'];
    $prescription_text = $_POST['prescription_text'];

    $stmt = $pdo->prepare("INSERT INTO prescriptions (appointmentId, prescription_text, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$appointmentId, $prescription_text]);

    header("Location: ../views/HealthcareProfessional/ePrescription.php?appointmentId=$appointmentId&success=1");
    exit();
}
?> -->

<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['professionalId'])) {
    die("Unauthorized");
}

$appointmentId = $_POST['appointmentId'] ?? null;
$prescription = $_POST['prescription_text'] ?? null;
$instructions = $_POST['instructions'] ?? null;
$created_at = date('Y-m-d H:i:s'); 

// if (!$appointmentId || !$prescription || !$instructions || !$created_at) {
//     die("Missing required fields.");
// }

try {
    $stmt = $pdo->prepare("INSERT INTO prescriptions (appointmentId, prescription_text, instructions, created_at) 
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$appointmentId, $prescription, $instructions, $created_at]);

    header("Location: ePrescription.php?success=1");
    exit();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
