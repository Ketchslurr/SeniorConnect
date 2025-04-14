<?php
include '../../config.php';

// Ensure user is logged in
session_start();
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

// Check if Google Fit token is saved
$userId = $_SESSION['userId'];
$stmt = $pdo->prepare("SELECT google_fit_access_token, google_fit_refresh_token FROM user_info WHERE userId = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || empty($user['google_fit_access_token']) || empty($user['google_fit_refresh_token'])) {
    // Redirect to OAuth flow
    header("Location: ../../api/login-google-fit.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Real-Time Monitoring</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">

    <?php include '../../includes/topbar.php'; ?>

    <div class="flex">
        <!-- Sidebar -->
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <h2 class="text-2xl font-semibold text-gray-700 mb-6">📊 Real-Time Health Monitoring</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Heart Rate Chart -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-red-600 mb-2">❤️ Heart Rate (BPM)</h3>
                    <canvas id="heartChart" height="150"></canvas>
                </div>

                <!-- Steps Chart -->
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-blue-600 mb-2">👣 Steps</h3>
                    <canvas id="stepsChart" height="150"></canvas>
                </div>

                <!-- Calories Chart -->
                <div class="bg-white p-4 rounded-lg shadow col-span-1 md:col-span-2">
                    <h3 class="text-lg font-semibold text-green-600 mb-2">🔥 Calories Burned</h3>
                    <canvas id="caloriesChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        const makeChart = (canvasId, label, color) => new Chart(document.getElementById(canvasId).getContext('2d'), {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label,
                    data: [],
                    borderColor: color,
                    backgroundColor: color + '33',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: { unit: 'minute' },
                        title: { display: true, text: 'Time' }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: label }
                    }
                }
            }
        });

        const heartChart = makeChart('heartChart', 'Heart Rate', 'rgb(239, 68, 68)');
        const stepsChart = makeChart('stepsChart', 'Steps', 'rgb(37, 99, 235)');
        const caloriesChart = makeChart('caloriesChart', 'Calories', 'rgb(34, 197, 94)');

        async function fetchData() {
            try {
                const res = await fetch('/api/googleFit/fetch-data.php');
                const data = await res.json();

                const times = data.map(dp => new Date(dp.time));

                heartChart.data.labels = stepsChart.data.labels = caloriesChart.data.labels = times;
                heartChart.data.datasets[0].data = data.map(dp => dp.bpm || null);
                stepsChart.data.datasets[0].data = data.map(dp => dp.steps || 0);
                caloriesChart.data.datasets[0].data = data.map(dp => dp.calories || 0);

                heartChart.update();
                stepsChart.update();
                caloriesChart.update();
            } catch (error) {
                console.error('Failed to fetch data:', error);
            }
        }

        fetchData();
        setInterval(fetchData, 10000); // every 10 seconds
    </script>

</body>
</html>
