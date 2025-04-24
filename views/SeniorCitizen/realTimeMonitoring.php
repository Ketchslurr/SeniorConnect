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
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>

    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>

        <div class="flex-1 p-6">
            <h2 class="text-2xl font-semibold text-gray-700 mb-6">📊 Real-Time Health Monitoring</h2>

            <!-- Filter + PDF Button -->
            <div class="mb-4 flex items-center gap-4">
                <div>
                    <label for="filter" class="block mb-1 font-medium text-gray-700">Time Filter:</label>
                    <select id="filter" class="border border-gray-300 rounded px-3 py-1">
                        <option value="10min">Last 10 Minutes</option>
                        <option value="1h">Last 1 Hour</option>
                        <option value="24h">Last 24 Hours</option>
                        <option value="7d">Last 7 Days</option>
                        <option value="30d">Last 30 Days</option>
                    </select>
                </div>

                <button onclick="fetchData()" class="mt-6 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Fetch Data
                </button>

                <a href="../../api/googleFit/generateHealthReport.php" target="_blank" class="mt-6 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                    📄 Download Health Report
                </a>
            </div>

            <!-- Numeric Values -->
            <div id="dataDisplay" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white shadow rounded p-6 text-center">
                    <h3 class="text-lg text-red-600 font-semibold mb-2">❤️ Heart Rate</h3>
                    <p id="bpmValue" class="text-3xl font-bold text-red-600">--</p>
                    <p class="text-sm text-gray-500">BPM</p>
                </div>

                <div class="bg-white shadow rounded p-6 text-center">
                    <h3 class="text-lg text-blue-600 font-semibold mb-2">👣 Steps</h3>
                    <p id="stepsValue" class="text-3xl font-bold text-blue-600">--</p>
                    <p class="text-sm text-gray-500">Today</p>
                </div>

                <div class="bg-white shadow rounded p-6 text-center">
                    <h3 class="text-lg text-green-600 font-semibold mb-2">🔥 Calories Burned</h3>
                    <p id="caloriesValue" class="text-3xl font-bold text-green-600">--</p>
                    <p class="text-sm text-gray-500">Total</p>
                </div>
            </div>

            <!-- Debug / Raw Output (optional) -->
            <pre id="result" class="hidden bg-white p-2 mt-4 rounded shadow text-sm text-gray-800"></pre>
        </div>
    </div>

    <script>
    async function fetchData() {
        const filter = document.getElementById('filter').value;

        try {
            const res = await fetch(`/api/googleFit/fetch-data.php?filter=${filter}`);
            if (!res.ok) {
                if (res.status === 401) {
                    window.location.href = '/api/login-google-fit.php';
                    return;
                } else {
                    throw new Error("API error: " + res.status);
                }
            }

            const data = await res.json().catch(() => {
                throw new Error("Invalid JSON response");
            });

            if (data.length === 0) {
                document.getElementById("bpmValue").textContent = "--";
                document.getElementById("stepsValue").textContent = "--";
                document.getElementById("caloriesValue").textContent = "--";
                return;
            }

            const latest = data[data.length - 1];
            document.getElementById("bpmValue").textContent = latest.bpm ?? '--';
            document.getElementById("stepsValue").textContent = latest.steps ?? '--';
            document.getElementById("caloriesValue").textContent = latest.calories ?? '--';

            document.getElementById("result").textContent = JSON.stringify(data, null, 2);

        } catch (error) {
            document.getElementById("result").textContent = "Error fetching data.";
            console.error("Fetch error:", error);
        }
    }

    setInterval(fetchData, 10000);
    fetchData();
    </script>
</body>
</html>
