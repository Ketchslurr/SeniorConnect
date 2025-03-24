<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['seniorId'])) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($_GET['fitnessId'])) {
    header("Location: senior_fitness.php");
    exit();
}

$fitnessId = $_GET['fitnessId'];
$seniorId = $_SESSION['seniorId'];

// Check if the senior purchased this class
$sql = "SELECT fc.* FROM fitness_purchases fp 
        JOIN fitness_classes fc ON fp.fitnessId = fc.fitnessId 
        WHERE fp.seniorId = :seniorId AND fc.fitnessId = :fitnessId AND fp.payment_status = 'Completed'";
$stmt = $pdo->prepare($sql);
$stmt->execute(['seniorId' => $seniorId, 'fitnessId' => $fitnessId]);
$class = $stmt->fetch();

if (!$class) {
    $_SESSION['error'] = "You haven't purchased this class.";
    header("Location: senior_fitness.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($class['title']) ?></title>
    <link rel="stylesheet" href="../../assets/styles.css"> <!-- Include your main styles -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-semibold text-gray-800"><?= htmlspecialchars($class['title']) ?></h2>
        <p class="text-gray-600 mt-2"><?= htmlspecialchars($class['description']) ?></p>

        <!-- Video Container -->
        <div class="mt-6 bg-white shadow-lg rounded-lg overflow-hidden p-4">
            <div class="relative w-full max-w-4xl mx-auto">
                <!-- Video Preview -->
                <video id="videoPlayer" class="w-full h-auto rounded-lg cursor-pointer" onclick="expandVideo()" controls>
                    <source src="../../<?= htmlspecialchars($class['video_url']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>

                <!-- Overlay Play Button (Optional) -->
                <div id="playOverlay" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40 text-white text-4xl font-bold opacity-0 transition-opacity">
                    ▶
                </div>
            </div>
        </div>
        
        <!-- Back Button -->
        <div class="mt-6">
            <a href="library.php" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                ← Back to Library
            </a>
        </div>
    </div>

    <script>
        function expandVideo() {
            var video = document.getElementById("videoPlayer");
            video.requestFullscreen().catch(err => {
                console.log("Fullscreen mode not supported", err);
            });
        }
    </script>
</body>
</html>
