<?php
session_start();
if (!isset($_SESSION["adminId"])) {
    header("Location: login.php");
    exit();
}

require '../../config.php';

if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    $stmt = $pdo->prepare("DELETE FROM user_info WHERE userId = ?");
    if ($stmt->execute([$userId])) {
        $_SESSION['success'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete user.";
    }
}

header("Location: manageUsers.php");
exit();
