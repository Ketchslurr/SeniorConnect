<?php
session_start();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="text-center bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold text-gray-700">You have been logged out.</h2>
        <p class="text-gray-600 mt-2">Redirecting you to the homepage...</p>
        <div class="mt-4">
            <a href="../index.php" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Go to Homepage</a>
        </div>
    </div>
    <script>
        setTimeout(() => {
            window.location.href = "../index.php";
        }, 3000);
    </script>
</body>
</html>
