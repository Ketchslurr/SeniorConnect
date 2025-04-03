<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId']) || !isset($_GET['id'])) {
    header("Location: forum.php");
    exit();
}

$topicId = $_GET['id'];

// Fetch topic details
$sql = "SELECT f.title, 
               IF(f.roleId = 2, s.fname, d.fname) AS author_name, 
               f.created_at, 
               f.roleId
        FROM forum f 
        LEFT JOIN seniorcitizen s ON f.userId = s.userId
        LEFT JOIN healthcareprofessional d ON f.userId = d.userId
        WHERE f.forumId = :topicId";

$stmt = $pdo->prepare($sql);
$stmt->execute(['topicId' => $topicId]);
$topic = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch replies
$sql = "SELECT r.message, r.created_at, 
               IF(r.roleId = 2, s.fname, d.fname) AS author_name, 
               r.roleId
        FROM forum_replies r 
        LEFT JOIN seniorcitizen s ON r.userId = s.userId
        LEFT JOIN healthcareprofessional d ON r.userId = d.userId
        WHERE r.topicId = :topicId 
        ORDER BY r.created_at ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['topicId' => $topicId]);
$replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($topic['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <!-- Sidebar Condition -->
        <?php 
        if ($_SESSION['role'] == 2) {
            include '../../includes/seniorCitizenSidebar.php'; // Senior Citizen Sidebar
        } elseif ($_SESSION['role'] == 3) {
            include '../../includes/healthcareProfessionalSidebar.php'; // Healthcare Professional Sidebar
        }
        ?>
        <div class="mb-4">
            <button onclick="window.history.back()" class="bg-blue-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-gray-700">
                ← Back
            </button>
        </div>
        <main class="flex-1 p-6">
            <h2 class="text-3xl font-bold"><?= htmlspecialchars($topic['title']) ?></h2>
            <p class="text-gray-600">By <?= htmlspecialchars($topic['author_name']?? '') ?> 
                (<?= $topic['roleId'] == 2 ? 'Senior' : 'Doctor' ?>) on <?= htmlspecialchars($topic['created_at']) ?>
            </p>

            <div class="mt-6">
                <h3 class="text-2xl font-bold">Replies</h3>
                <ul class="space-y-4">
                    <?php if (!empty($replies)) {
                        foreach ($replies as $reply) { ?>
                            <li class="border-b pb-2">
                                <p><?= htmlspecialchars($reply['message']) ?></p>
                                <p class="text-sm text-gray-600">By <?= htmlspecialchars($reply['author_name']) ?> 
                                    (<?= $reply['roleId'] == 2 ? 'Senior' : 'Doctor' ?>) 
                                    on <?= htmlspecialchars($reply['created_at']) ?>
                                </p>
                            </li>
                        <?php } 
                    } else { ?>
                        <p>No replies yet. Be the first to reply!</p>
                    <?php } ?>
                </ul>
            </div>

            <!-- Reply Form -->
            <div class="mt-6 bg-white p-4 shadow rounded">
                <h3 class="text-xl font-bold mb-3">Post a Reply</h3>
                <form action="post_reply.php" method="POST">
                    <input type="hidden" name="topicId" value="<?= $topicId ?>">
                    <textarea name="message" required placeholder="Type your reply..." 
                        class="w-full p-2 border rounded"></textarea>
                    <button type="submit" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Submit Reply
                    </button>
                </form>
            </div>
        </main>

    </div>
</body>
</html>
