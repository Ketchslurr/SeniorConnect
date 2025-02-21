<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

// Get the current date
$currentDate = date('Y-m-d');

// Fetch upcoming appointments
$sql = "SELECT * FROM healthappointments WHERE appointment_date >= :currentDate ORDER BY appointment_date ASC LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute(['currentDate' => $currentDate]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare events for FullCalendar
$events = [];
foreach ($appointments as $row) {
    $events[] = [
        'title' => "Appointment ID: " . htmlspecialchars($row['appointmentId']),
        'start' => htmlspecialchars($row['appointment_date']),
        'color' => '#007bff', // Blue color for events
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>

    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>

        <main class="flex-1 p-6">
            <h2 class="text-3xl font-bold text-center mb-6">Welcome, Senior User!</h2>
            <p class="text-gray-700 text-center mb-6">Your health data and monitoring system is available here.</p>

            <!-- Centered Book Now Button -->
            <div class="flex items-center justify-center mb-6">
                <button onclick="window.location.href='../SeniorCitizen/telehealth.php'"
                    class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-lg">
                    BOOK NOW
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Notifications Section -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-2xl font-bold mb-4">Notifications</h3>
                    <div class="border-l-4 border-red-500 bg-red-100 p-4 mb-3">
                        <p><strong>Urgent:</strong> Your appointment is scheduled for <strong>October 15, 2023</strong> at <strong>10:00 AM</strong>. Please confirm your attendance.</p>
                    </div>
                    <div class="border-l-4 border-blue-500 bg-blue-100 p-4">
                        <p><strong>Upcoming:</strong> Your telehealth session is confirmed for <strong>October 20, 2023</strong>.</p>
                    </div>
                    </div>
                    <!-- Upcoming Appointments Section -->
                    <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                        <h3 class="text-2xl font-bold mb-4">Upcoming Appointments</h3>
                        <div class="space-y-4">
                            <?php if (!empty($appointments)) { 
                                foreach ($appointments as $row) { ?>
                                    <div class="border-l-4 border-green-500 bg-green-100 p-4">
                                        <p><strong>Appointment ID:</strong> <?= htmlspecialchars($row['appointmentId']) ?></p>
                                        <p><strong>Senior Citizen ID:</strong> <?= htmlspecialchars($row['seniorCitizenId']) ?></p>
                                        <p><strong>Healthcare ID:</strong> <?= htmlspecialchars($row['healthcareId']) ?></p>
                                        <p><strong>Date:</strong> <?= htmlspecialchars($row['appointment_date']) ?></p>
                                        <p><strong>Status:</strong> <?= htmlspecialchars($row['appointment_status']) ?></p>
                                    </div>
                                <?php } 
                            } else { ?>
                                <div class="border-l-4 border-gray-500 bg-gray-100 p-4">
                                    <p>No upcoming appointments.</p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Calendar Section -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-2xl font-bold mb-4">Your Appointments Calendar</h3>
                    <div id="calendar"></div>
                </div>
            </div>

            
        </main>
    </div>

    <!-- FullCalendar Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'en',
                events: <?= json_encode($events) ?>,
            });
            calendar.render();
        });
    </script>
</body>
</html>
