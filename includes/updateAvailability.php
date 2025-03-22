<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    echo "Unauthorized";
    exit();
}

$doctorId = $_SESSION['professionalId'];
$isAvailable = isset($_POST['is_available']) ? (int)$_POST['is_available'] : 0;

// Check if doctor already exists in the available_doctors table
$query = "SELECT * FROM available_doctors WHERE professionalId = :doctorId";
$stmt = $pdo->prepare($query);
$stmt->execute(['doctorId' => $doctorId]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if ($doctor) {
    // Update availability
    $updateQuery = "UPDATE available_doctors SET is_available = :isAvailable WHERE professionalId = :doctorId";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute(['isAvailable' => $isAvailable, 'doctorId' => $doctorId]);
} else {
    // Insert new entry if not found
    $insertQuery = "INSERT INTO available_doctors (professionalId, is_available) VALUES (:doctorId, :isAvailable)";
    $insertStmt = $pdo->prepare($insertQuery);
    $insertStmt->execute(['doctorId' => $doctorId, 'isAvailable' => $isAvailable]);
}
header("Location: ../views/HealthcareProfessional/services.php?success=Availability changed successfully");
echo "Availability updated successfully.";
?>
