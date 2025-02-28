<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['serviceId'])) {
    $serviceId = $_GET['serviceId'];

    // Fetch service details
    $sql = "SELECT * FROM services WHERE serviceId = :serviceId";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['serviceId' => $serviceId]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch available days
    $daysQuery = "SELECT day FROM service_availability WHERE service_id = :service_id";
    $stmt = $pdo->prepare($daysQuery);
    $stmt->execute(['service_id' => $serviceId]);
    $availableDays = $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch as array

    // Merge service details with available days
    $service['available_days'] = $availableDays;

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($service);
}

