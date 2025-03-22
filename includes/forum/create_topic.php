<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $userId = $_SESSION['userId'];
    $roleId = $_SESSION['role']; // Use 'roleId' instead of 'role' for consistency

    if (!empty($title)) {
        $sql = "INSERT INTO forum (title, userId, roleId, created_at) VALUES (:title, :userId, :roleId, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['title' => $title, 'userId' => $userId, 'roleId' => $roleId]);

        // Get the last inserted topic ID
        $topicId = $pdo->lastInsertId();

        // Redirect to the newly created topic
        header("Location: ../../views/Forum/forum_topic.php?id=" . $topicId);
        exit();
    }
}

// If something goes wrong, redirect back to the forum page
header("Location: ../../views/Forum/forum.php");
exit();
?>
