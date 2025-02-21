<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: ../login.php");
    exit();
}

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['serviceId'])) {
    $serviceId = $_POST['serviceId'];
    $userId = $_SESSION['userId'];

    try {
        // Verify that the service belongs to the logged-in user
        $checkQuery = "SELECT * FROM services WHERE serviceId = :serviceId AND professionalId = :userId";
        $checkStmt = $pdo->prepare($checkQuery);
        $checkStmt->execute(['serviceId' => $serviceId, 'userId' => $userId]);
        $service = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($service) {
            // Proceed to delete the service
            $deleteQuery = "DELETE FROM services WHERE serviceId = :serviceId AND professionalId = :userId";
            $deleteStmt = $pdo->prepare($deleteQuery);
            $deleteStmt->execute(['serviceId' => $serviceId, 'userId' => $userId]);

            $_SESSION['success'] = "Service deleted successfully.";
        } else {
            $_SESSION['error'] = "Service not found or unauthorized deletion.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting service: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

// Redirect back to the services management page
header("Location: ../views/healthcareProfessional/services.php");
exit();
