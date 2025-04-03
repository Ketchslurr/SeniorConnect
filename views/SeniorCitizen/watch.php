<?php
if (!isset($_GET['videoId'])) {
    echo "Invalid video ID.";
    exit();
}

$videoId = htmlspecialchars($_GET['videoId']); // Prevent XSS
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Video</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold text-center mb-4">Senior Fitness Video</h1>

        <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg p-4">
            <!-- YouTube Video Embed -->
            <div class="relative w-full aspect-w-16 aspect-h-9">
                <iframe class="w-full h-64 md:h-96 rounded-lg" 
                        src="https://www.youtube.com/embed/<?= $videoId ?>" 
                        frameborder="0" allowfullscreen>
                </iframe>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="seniorFitness.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                Back to Fitness Classes
            </a>
        </div>
    </div>
</body>
</html>
