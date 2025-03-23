<?php
session_start();
include '../config.php';
if (!isset($_SESSION['userId'])) {
    header("Location: ../login.php");
    exit();
}
// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = $_POST['serviceId'];
    $professionalId = $_SESSION['professionalId'];
    try {
        // Verify that the service belongs to the logged-in user
        $checkQuery = "SELECT * FROM services WHERE serviceId = :serviceId AND professionalId = :professionalId";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute(['serviceId' => $serviceId, 'professionalId' => $professionalId]);
        $service = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($service) {
            // Proceed to delete the service
            $deleteQuery = "DELETE FROM services WHERE serviceId = :serviceId AND professionalId = :professionalId";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->execute(['serviceId' => $serviceId, 'professionalId' => $professionalId]);

            $_SESSION['success'] = "Service deleted successfully.";
        } else {
            $_SESSION['error'] = "Service not found or unauthorized deletion.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting service: " . $e->getMessage();
    }
} else {
    echo "error";
    $_SESSION['error'] = "Invalid request.";
}
// Redirect back to the services management page
header("Location: ../views/healthcareProfessional/services.php?success=Service deleted successfully");
exit();
