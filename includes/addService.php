<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $professionalId = $_SESSION['userId']; // Assuming userId maps to healthcareId
    $serviceName = $_POST['service_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    // $duration = $_POST['duration'];

    $sql = "INSERT INTO services (professionalId, service_name, description, price) VALUES (:professionalId, :service_name, :description, :price)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([
        'professionalId' => $professionalId,
        'service_name' => $serviceName,
        'description' => $description,
        'price' => $price,
        // 'duration' => $duration
    ])) {
        header("Location: ../views/HealthcareProfessional/services.php?success=Service added successfully");
        echo "<script>alert('Service added successfully!');</script>";
        exit();
    } else {
        echo "Error adding service.";
    }
}
