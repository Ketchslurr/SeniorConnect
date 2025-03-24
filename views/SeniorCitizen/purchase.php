<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fitnessId'])) {
    $fitnessId = $_POST['fitnessId'];
    $seniorId = $_SESSION['seniorId'];

    // Insert purchase record
    $sql = "INSERT INTO fitness_purchases (seniorId, fitnessId, payment_status) VALUES (:seniorId, :fitnessId, 'Completed')";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute(['seniorId' => $seniorId, 'fitnessId' => $fitnessId])) {
        $_SESSION['success'] = "Payment successful! You can now watch the video.";
    } else {
        $_SESSION['error'] = "Payment failed.";
    }
}

header("Location: watch.php");
exit();
?>
