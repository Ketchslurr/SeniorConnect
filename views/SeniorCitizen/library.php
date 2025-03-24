<?php
include '../../config.php';

// Ensure user is logged in
session_start();
if (!isset($_SESSION['seniorId'])) {
    header("Location: login.php");
    exit();
}

$seniorId = $_SESSION['seniorId'];

// Fetch Purchased Videos
$sqlPurchased = "SELECT fc.fitnessId, fc.title, fc.video_url FROM fitness_purchases p
                 JOIN fitness_classes fc ON p.fitnessId = fc.fitnessId
                 WHERE p.seniorId = :seniorId";
$stmtPurchased = $pdo->prepare($sqlPurchased);
$stmtPurchased->execute(['seniorId' => $seniorId]);
$purchasedVideos = $stmtPurchased->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchased Videos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>

    <div class="flex">
        <!-- Sidebar -->
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">🎥 My Purchased Videos</h2>

            <?php if (!empty($purchasedVideos)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($purchasedVideos as $video): ?>
                        <div class="bg-white shadow-md rounded-lg overflow-hidden">
                            <div class="relative w-full h-48">
                                <video class="w-full h-full object-cover preview-video" muted loop>
                                    <source src="../../<?= htmlspecialchars($video['video_url']) ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <button class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 text-white text-lg font-semibold opacity-0 hover:opacity-100 transition" onclick="openVideo('<?= $video['fitnessId'] ?>')">
                                    ▶ Watch Full Video
                                </button>
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-semibold"><?= htmlspecialchars($video['title']) ?></h3>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center mt-6">You haven't purchased any videos yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Play video preview on hover
        document.querySelectorAll('.preview-video').forEach(video => {
            video.addEventListener('mouseover', () => video.play());
            video.addEventListener('mouseleave', () => video.pause());
        });

        // Open full video
        function openVideo(fitnessId) {
            window.location.href = 'watch.php?fitnessId=' + fitnessId;
        }
    </script>
</body>
</html>
