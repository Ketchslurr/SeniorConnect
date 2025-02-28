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
$sql = "SELECT * FROM appointment WHERE appointment_date >= :currentDate ORDER BY appointment_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['currentDate' => $currentDate]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare events for FullCalendar
$events = [];
foreach ($appointments as $row) {
    $events[] = [
        'title' => htmlspecialchars($row['service_name']) . " - " . htmlspecialchars($row['appointment_time']),
        'start' => htmlspecialchars($row['appointment_date']),
        'id' => htmlspecialchars($row['appointmentId']),
        'color' => '#007bff', // Blue color for events
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>

    
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>

    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>

        <main class="flex-1 p-6 flex flex-col items-center">
            <h3 class="text-3xl font-bold mb-6 text-center">Your Appointments Calendar</h3>
            <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-4xl">
                <div id="calendar"></div>
            </div>
        </main>
    </div>

    <!-- Modal -->
   
<div id="appointmentModal" class="fixed inset-0 hidden bg-gray-900 bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96 relative z-50">
        <h3 class="text-xl font-bold mb-4">Appointment Details</h3>
        <p><strong>Service:</strong> <span id="modalService"></span></p>
        <p><strong>Time:</strong> <span id="modalTime"></span></p>
        <p><strong>Date:</strong> <span id="modalDate"></span></p>
        <p><strong>Google Meet:</strong> <a id="meetLink" href="#" target="_blank" class="text-blue-600 underline">Join Meeting</a></p>
        <button onclick="closeModal()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded">Close</button>
    </div>
</div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'en',
                height: 'auto',
                events: <?= json_encode($events) ?>,
                eventClick: function(info) {
                    var details = info.event.title.split(" - ");
                    document.getElementById('modalService').textContent = details[0];
                    document.getElementById('modalTime').textContent = details[1];
                    document.getElementById('modalDate').textContent = info.event.start.toISOString().split('T')[0];
                    document.getElementById('meetLink').href = "https://meet.google.com/" + info.event.id;
                    document.getElementById('appointmentModal').classList.remove('hidden');
                },
                eventMouseEnter: function(info) {
                    info.el.style.cursor = 'pointer';  // Change cursor to pointer on hover
                },
                eventMouseLeave: function(info) {
                    info.el.style.cursor = 'default';  // Revert to default when not hovering
                }
            });
            calendar.render();
        });

        function closeModal() {
            document.getElementById('appointmentModal').classList.add('hidden');
        }
    </script>
</body>
</html>
