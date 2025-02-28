<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceName = $_POST['service_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $professionalId = $_SESSION['professionalId'];

    $sql = "INSERT INTO services (professionalId, service_name, description, price) VALUES (:professionalId, :service_name, :description, :price)";
    $stmt = $pdo->prepare($sql);

    // Insert into services table
    $sql = "INSERT INTO services (service_name, description, price, professionalId) 
    VALUES (:service_name, :description, :price, :professionalId)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
    'service_name' => $serviceName,
    'description' => $description,
    'price' => $price,
    'professionalId' => $professionalId
    ]);
      // Get the last inserted service_id
      $serviceId = $pdo->lastInsertId();

    // Insert availability days into service_availability table
    if (isset($_POST['days'])) {
        foreach ($_POST['days'] as $day) {
           
            $sql = "INSERT INTO service_availability (service_id, day) VALUES (:service_id, :day)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'service_id' => $serviceId,
                'day' => $day
            ]);
        }
    }
        header("Location: ../views/HealthcareProfessional/services.php?success=Service added successfully");
        echo "<script>alert('Service added successfully!');</script>";
        exit();
    } else {
        echo "Error adding service.";
    }

