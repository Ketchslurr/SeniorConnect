<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId']) || !isset($_GET['id'])) {
    header("Location: forum.php");
    exit();
}

$topicId = $_GET['id'];
$userId = $_SESSION['userId'];

// Check if user is the author
$sql = "SELECT * FROM forum WHERE forumId = :topicId AND userId = :userId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['topicId' => $topicId, 'userId' => $userId]);
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

// If not found, redirect back
if (!$topic) {
    header("Location: forum.php");
    exit();
}

// Delete topic
$sql = "DELETE FROM forum WHERE forumId = :topicId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['topicId' => $topicId]);

// Redirect back to forum
header("Location: forum.php");
exit();
?>
