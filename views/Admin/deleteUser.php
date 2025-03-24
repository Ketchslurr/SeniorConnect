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
    $stmt->execute([$userId]);
}

header("Location: manage_users.php");
exit();
?>
