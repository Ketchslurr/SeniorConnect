<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['professionalId'])) {
    header("Location: login.php");
    exit();
}
$professionalId = $_SESSION['professionalId'];
$currentDate = date('Y-m-d');

// Fetch upcoming appointments
$sql = "SELECT ha.*, sc.fName,sc.lName,sc.gender, sc.age 
        FROM appointment ha
        JOIN seniorcitizen sc ON ha.seniorId = sc.seniorId
        WHERE ha.professionalId = :professionalId AND ha.appointment_date >= :currentDate 
        AND ha.appointment_status != 'Cancelled'
        ORDER BY ha.appointment_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['professionalId' => $professionalId, 'currentDate' => $currentDate]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare events for FullCalendar
$events = [];
foreach ($appointments as $row) {
    $events[] = [
        // 'title' => "Appointment ID: " . htmlspecialchars($row['appointmentId']),
        'title' => "Appointment ID: REF-" . str_pad($row['appointmentId'], 6, '0', STR_PAD_LEFT),
        'start' => htmlspecialchars($row['appointment_date']),
        'color' => '#007bff',
        'url' => 'appointments.php?appointmentId=' . htmlspecialchars($row['appointmentId'])
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>

    <div class="flex">
        <?php include '../../includes/healthcareProfessionalSidebar.php'; ?>

        <main class="flex-1 p-6">
            <h2 class="text-3xl font-bold text-center mb-6">Welcome, Healthcare Professional!</h2>
            <p class="text-gray-700 text-center mb-6">Your health data and monitoring system is available here.</p>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upcoming Appointments Section -->
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-2xl font-bold mb-4">Upcoming Appointments</h3>
                    <div class="space-y-4">
                        <?php if (!empty($appointments)) { 
                            foreach ($appointments as $row) { ?>
                                <a href="appointments.php?appointmentId=<?= htmlspecialchars($row['appointmentId']) ?>" class="block">
                                    <div class="border-l-4 border-green-500 bg-green-100 p-4 hover:bg-green-200 transition">
                                        <!-- <p><strong>Appointment ID:</strong> <//?= htmlspecialchars($row['appointmentId']) ?></p> -->
                                        <p><strong>Appointment ID:</strong> <?= 'REF-' . str_pad($row['appointmentId'], 6, '0', STR_PAD_LEFT) ?></p>
                                        <p><strong>Patient Name:</strong> <?= htmlspecialchars($row['fName'] . ' ' . $row['lName']) ?></p>
                                        <p><strong>Age:</strong> <?= htmlspecialchars($row['age']) ?></p>
                                        <p><strong>Gender:</strong> <?= htmlspecialchars($row['gender']) ?></p>
                                        <p><strong>Date:</strong> <?= htmlspecialchars($row['appointment_date']) ?></p>
                                        <p><strong>Status:</strong> <?= htmlspecialchars($row['appointment_status']) ?></p>
                                    </div>
                                </a>
                            <?php } 
                        } else { ?>
                            <div class="border-l-4 border-gray-500 bg-gray-100 p-4">
                                <p>No upcoming appointments.</p>
                            </div>
                        <?php } ?>
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
                eventClick: function(info) {
                    window.location.href = info.event.url;
                    info.jsEvent.preventDefault();
                }
            });
            calendar.render();
        });
    </script>
</body>
</html>
