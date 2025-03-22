<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId']) || !isset($_GET['id'])) {
    header("Location: forum.php");
    exit();
}

$topicId = $_GET['id'];
$userId = $_SESSION['userId'];

// Fetch topic
$sql = "SELECT * FROM forum WHERE forumId = :topicId AND userId = :userId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['topicId' => $topicId, 'userId' => $userId]);
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

// If topic not found, redirect back
if (!$topic) {
    header("Location: forum.php");
    exit();
}

// Update topic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    
    if (!empty($title)) {
        $sql = "UPDATE forum SET title = :title WHERE forumId = :topicId";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['title' => $title, 'topicId' => $topicId]);

        header("Location: forum.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Topic</title>
</head>
<body>
    <h2>Edit Topic</h2>
    <form method="POST">
        <input type="text" name="title" value="<?= htmlspecialchars($topic['title']) ?>" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>
