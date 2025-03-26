<?php
session_start();
require '../../config.php'; // Adjust path if needed

// Check if the user is logged in
if (!isset($_SESSION["userId"])) {
    $_SESSION['error'] = "You must be logged in to reply.";
    header("Location: forum.php"); // Redirect to forum
    exit();
}

// Validate POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $topicId = $_POST['topicId'] ?? null;
    $message = trim($_POST['message'] ?? '');
    $userId = $_SESSION["userId"];
    $roleId = $_SESSION["role"]; // 2 = Senior, 3 = Doctor

    //  Check if required fields are filled
    if (!$topicId || empty($message)) {
        $_SESSION['error'] = "Reply cannot be empty.";
        header("Location: forum_topic.php?id=" . $topicId);
        exit();
    }

    try {
        //  Insert reply into `forum_replies`
        $stmt = $pdo->prepare("INSERT INTO forum_replies (topicId, userId, roleId, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$topicId, $userId, $roleId, $message]);

        $_SESSION['success'] = "Reply posted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error posting reply: " . $e->getMessage();
    }

    //  Redirect back to the forum topic
    header("Location: forum_topic.php?id=" . $topicId);
    exit();
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: forum.php");
    exit();
}
?>
