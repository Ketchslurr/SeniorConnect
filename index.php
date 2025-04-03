<?php
include 'config.php';

// Fetch active doctors
$activeDoctors = $pdo->query("SELECT COUNT(*) FROM available_doctors WHERE is_available = '1'")->fetchColumn();

// Fetch active users (excluding admin)
$activeUsers = $pdo->query("SELECT COUNT(*) FROM user_info WHERE roleId IN (2, 3)")->fetchColumn();

// Fetch total appointments
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointment")->fetchColumn();
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
                        <div class="text-2xl font-bold text-blue-600"><?= $activeDoctors ?></div>
                        <h3 class="text-gray-600 mt-2">Active Doctors</h3>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-md text-center">
                        <div class="text-2xl font-bold text-blue-600"><?= $activeUsers ?></div>
                        <h3 class="text-gray-600 mt-2">Active Users</h3>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-md text-center">
                        <div class="text-2xl font-bold text-blue-600"><?= $totalAppointments ?></div>
                        <h3 class="text-gray-600 mt-2">Total Appointments</h3>
                    </div>
                </div>
            </div>
            <div class="md:w-1/2">
                <img src="assets/Images/image.png" alt="Healthcare illustration" 
                     class="w-full max-w-xl mx-auto">
            </div>
        </div>
    </main>
</body>
</html>
