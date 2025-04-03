<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) { 
    header("Location: ../../login.php");
    exit();
}

$apiKey = 'AIzaSyC5nucTs5Qk3cWD8_M3hJZWwiYyDdlTPAA'; 
$defaultQuery = 'senior fitness workouts';
$searchQuery = isset($_GET['search']) ? urlencode($_GET['search']) : urlencode($defaultQuery);

$apiUrl = "https://www.googleapis.com/youtube/v3/search?part=snippet&type=video&q={$searchQuery}&maxResults=10&key={$apiKey}";
$response = file_get_contents($apiUrl);
$videos = json_decode($response, true)['items'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Fitness Classes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    
    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>

        <div class="flex-1 p-6">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Fitness Classes</h2>
            
            <!-- Search Bar -->
            <form method="GET" action="" class="mb-6 flex">
                <input type="text" name="search" placeholder="Search fitness videos..." class="w-full p-2 border rounded-l" required>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-700">Search</button>
            </form>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($videos as $video) : ?>
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <img class="w-full h-48 object-cover" src="<?= htmlspecialchars($video['snippet']['thumbnails']['medium']['url']) ?>" alt="Video Thumbnail">
                        <div class="p-4">
                            <h3 class="text-lg font-semibold">
                                <?= htmlspecialchars($video['snippet']['title']) ?>
                            </h3>
                            <p class="text-gray-600 text-sm">
                                <?= htmlspecialchars($video['snippet']['description']) ?>
                            </p>
                            <a href="watch.php?videoId=<?= htmlspecialchars($video['id']['videoId']) ?>" 
                               class="block mt-2 bg-green-500 text-white text-center px-4 py-2 rounded hover:bg-green-600 transition">
                                Watch Now
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($videos)) : ?>
                    <p class="text-center text-gray-500 p-4 col-span-full">No fitness videos found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
