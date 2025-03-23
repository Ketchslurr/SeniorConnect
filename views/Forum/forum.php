<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['userId'];
$roleId = $_SESSION['role']; // Ensure consistency with database
$seniorId = $_SESSION['seniorId']; 

// Fetch all forum topics
$sql = "SELECT f.*, 
       CASE 
           WHEN f.roleId = 2 THEN CONCAT(s.fName, ' ', s.lName) 
           WHEN f.roleId = 3 THEN CONCAT(d.fName, ' ', d.lName) 
           ELSE 'Unknown' 
       END AS author_name
FROM forum f
LEFT JOIN seniorcitizen s ON f.roleId = 2 AND f.userId = s.seniorId
LEFT JOIN healthcareprofessional d ON f.roleId = 3 AND f.userId = d.professionalId
ORDER BY f.created_at DESC
LIMIT 25;";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
       <!-- Sidebar Condition -->
       <?php 
        if ($roleId == 2) {
            include '../../includes/seniorCitizenSidebar.php'; // Senior Citizen Sidebar
        } elseif ($roleId == 3) {
            include '../../includes/healthcareProfessionalSidebar.php'; // Healthcare Professional Sidebar
        }
        ?>

        <main class="flex-1 p-6">
            <h2 class="text-3xl font-bold text-center mb-6">Community Forum</h2>

            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-2xl font-bold mb-4">Start a Discussion</h3>
                <form action="../../includes/forum/create_topic.php" method="POST" class="mb-6">
                    <input type="text" name="title" placeholder="Enter topic title..." required class="w-full p-2 border rounded">
                    <button type="submit" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Post Topic</button>
                </form>

                <h3 class="text-2xl font-bold mb-4">Recent Topics</h3>
                <ul class="space-y-4">
                    <?php if (!empty($topics)) {
                        foreach ($topics as $topic) { ?>
                            <li class="border-b pb-2 flex justify-between items-center">
                                <div>
                                    <a href="SeniorCitizen/forum_topic.php?id=<?= $topic['forumId'] ?>" class="text-blue-500 font-semibold hover:underline">
                                        <?= htmlspecialchars($topic['title']) ?>
                                    </a>
                                    <p class="text-sm text-gray-600">By <?= htmlspecialchars($topic['author_name']) ?> 
                                        (<?= $topic['roleId'] == 2 ? 'Senior' : 'Doctor' ?>) 
                                        on <?= htmlspecialchars($topic['created_at']) ?>
                                    </p>
                                </div>
                                
                                <!-- Show Edit/Delete icons if the logged-in user is the author -->
                                <?php if ($topic['userId'] == $userId) { ?>
                                    <div class="flex space-x-2">
                                        <!-- Edit Button -->
                                        <a href="edit_topic.php?id=<?= $topic['forumId'] ?>" class="text-yellow-500 hover:text-yellow-700">
                                            ✏️
                                        </a>
                                        <!-- Delete Button -->
                                        <a href="delete_topic.php?id=<?= $topic['forumId'] ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure you want to delete this topic?');">
                                            🗑️
                                        </a>
                                    </div>
                                <?php } ?>
                            </li>
                        <?php }
                    } else { ?>
                        <p>No topics yet. Be the first to start a discussion!</p>
                    <?php } ?>
                </ul>
            </div>
        </main>
    </div>
</body>
</html>
