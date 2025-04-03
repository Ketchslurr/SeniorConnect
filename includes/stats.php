<?php
include 'config.php';

$response = [
    'activeDoctors' => 0,
    'activeUsers' => 0,
    'totalAppointment' => 0
];

try {
    // Count active doctors
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE roleId = 3"); // Adjust based on your database structure
    $response['activeDoctors'] = $stmt->fetchColumn();

    // Count active users (seniors)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE roleId = 2"); // Adjust based on your database structure
    $response['activeUsers'] = $stmt->fetchColumn();

    // Count medical partners
    $stmt = $pdo->query("SELECT COUNT(*) FROM appointment"); // Adjust if you have a medical partners table
    $response['totalAppointment'] = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
