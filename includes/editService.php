<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $serviceId = $_POST['serviceId'];
    $serviceName = $_POST['service_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    // $duration = $_POST['duration'];

    $sql = "UPDATE services SET service_name = :service_name, description = :description, price = :price WHERE serviceId = :serviceId";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([
        'service_name' => $serviceName,
        'description' => $description,
        'price' => $price,
        // 'duration' => $duration,
        'serviceId' => $serviceId
    ])) {
        header("Location: services.php?success=Service updated successfully");
        exit();
    } else {
        echo "Error updating service.";
    }
}