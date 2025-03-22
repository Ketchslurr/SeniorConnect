<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['seniorId']) || $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: forum.php");
    exit();
}

$topicId = $_POST['topicId'];
$message = trim($_POST['message']);
$userId = $_SESSION['userId'];

if (!empty($message)) {
    $sql = "INSERT INTO forum_replies (topicId, userId, message, created_at) VALUES (:topicId, :userId, :message, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['topicId' => $topicId, 'userId' => $userId, 'message' => $message]);
}

header("Location: forum_topic.php?id=$topicId");
exit();
?>
