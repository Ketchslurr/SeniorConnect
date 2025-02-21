<?php
include 'config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: views/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smartwatch Healthcare</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation Bar -->
    <header class="bg-blue-600 text-white">
        <nav class="container mx-auto px-6 py-4 flex items-center justify-between">
            <div class="logo">
                <a href="index.php" class="text-2xl font-bold">
                    <span class="text-cyan-300">Senior</span> Connect
                </a>
            </div>
            
            <div class="hidden md:flex space-x-8">
                <a href="index.php" class="hover:text-cyan-200 transition-colors">Home</a>
                <a href="#" class="hover:text-cyan-200 transition-colors">About us</a>
                <a href="#" class="hover:text-cyan-200 transition-colors">Services</a>
            </div>

            <div class="flex items-center space-x-4">
                <button onclick="window.location.href='views/login.php'" 
                        class="px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                    Log in
                </button>
                <button onclick="window.location.href='views/signup.php'" 
                        class="px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                    Sign up
                </button>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="md:w-1/2 mb-12 md:mb-0">
                <h2 class="text-4xl md:text-5xl font-bold mb-6">
                    We Are Ready to <br>
                    <span class="text-blue-600">Help Your Health</span><br>
                    Problems
                </h2>
                
                <p class="text-gray-600 mb-8 text-lg">
                    In times like today, your health is very important especially for senior care. 
                    We're here to help you with health consultations and monitoring.
                </p>

                <button onclick="window.location.href='views/login.php'"
                        class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-lg">
                    START NOW
                </button>

                <div class="mt-12 grid grid-cols-3 gap-6">
                    <div class="bg-white p-4 rounded-lg shadow-md text-center">
                        <div class="text-2xl font-bold text-blue-600" id="countOne">0</div>
                        <h3 class="text-gray-600 mt-2">Active Doctors</h3>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-md text-center">
                        <div class="text-2xl font-bold text-blue-600" id="countTwo">0</div>
                        <h3 class="text-gray-600 mt-2">Active Users</h3>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-md text-center">
                        <div class="text-2xl font-bold text-blue-600" id="countThree">0</div>
                        <h3 class="text-gray-600 mt-2">Medical Partners</h3>
                    </div>
                </div>
            </div>

            <div class="md:w-1/2">
                <img src="assets/Images/image.png" alt="Healthcare illustration" 
                     class="w-full max-w-xl mx-auto">
            </div>
        </div>
    </main>

    <script>
        // Counting animation function
        function countTo(targetNumber, duration, elementId) {
            const countElement = document.getElementById(elementId);
            let startNumber = 0;
            const increment = targetNumber / (duration / 100);
            const interval = setInterval(() => {
                startNumber += increment; 
                if (startNumber >= targetNumber) {
                    clearInterval(interval);
                    startNumber = targetNumber; 
                }
                countElement.textContent = Math.floor(startNumber); 
            }, 100);
        }

        // Start animations after page load
        window.addEventListener('DOMContentLoaded', (event) => {
            countTo(5, 3000, 'countOne');
            countTo(200, 3000, 'countTwo');
            countTo(10, 3000, 'countThree');
        });
    </script>
</body>
</html>