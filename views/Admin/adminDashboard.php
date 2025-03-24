<?php
session_start();
if (!isset($_SESSION["adminId"])) {
    header("Location: login.php");
    exit();
}

require '../../config.php';

// Fetch total statistics
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointment")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM user_info")->fetchColumn();
$totalProfessionals = $pdo->query("SELECT COUNT(*) FROM healthcareprofessional")->fetchColumn();

// Fetch users breakdown
$seniorCount = $pdo->query("SELECT COUNT(*) FROM user_info WHERE roleId = 2")->fetchColumn();
$doctorCount = $pdo->query("SELECT COUNT(*) FROM user_info WHERE roleId = 3")->fetchColumn();

// Fetch appointment status counts
$pendingAppointments = $pdo->query("SELECT COUNT(*) FROM appointment WHERE appointment_status = 'Pending'")->fetchColumn();
$acceptedAppointments = $pdo->query("SELECT COUNT(*) FROM appointment WHERE appointment_status = 'Confirmed'")->fetchColumn();
$declinedAppointments = $pdo->query("SELECT COUNT(*) FROM appointment WHERE appointment_status = 'Cancelled'")->fetchColumn();

// Fetch monthly appointments
$monthlyAppointments = $pdo->query("
    SELECT MONTH(appointment_date) AS month, COUNT(*) AS count 
    FROM appointment 
    GROUP BY MONTH(appointment_date)
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch most requested services
$requestedServices = $pdo->query("
    SELECT service_name, COUNT(*) AS count 
    FROM appointment 
    GROUP BY service_name 
    ORDER BY count DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch doctor availability
$availableDoctors = $pdo->query("SELECT COUNT(*) FROM available_doctors WHERE is_available = '1'")->fetchColumn();
$unavailableDoctors = $pdo->query("SELECT COUNT(*) FROM available_doctors WHERE is_available = '0'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <?php include '../../includes/adminTopbar.php'; ?>

    <div class="flex">
        <?php include '../../includes/adminSidebar.php'; ?>

        <div class="flex-1 p-8">
            <h2 class="text-3xl font-bold text-blue-900 mb-6">Admin Dashboard</h2>

            <!-- Cards Summary -->
            <div class="grid grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-600 text-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-lg font-semibold">Total Appointments</h3>
                    <p class="text-3xl font-bold"><?= $totalAppointments ?></p>
                </div>
                <div class="bg-blue-600 text-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-lg font-semibold">Total Users</h3>
                    <p class="text-3xl font-bold"><?= $totalUsers ?></p>
                </div>
                <div class="bg-blue-600 text-white p-6 rounded-lg shadow-lg">
                    <h3 class="text-lg font-semibold">Total Professionals</h3>
                    <p class="text-3xl font-bold"><?= $totalProfessionals ?></p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-2 gap-8">
                <div class="bg-white p-6 shadow-lg rounded-lg flex flex-col items-center">
                    <h3 class="text-xl font-semibold text-blue-700 mb-4">User Breakdown</h3>
                    <canvas id="userBreakdownChart" style="max-width: 400px; max-height: 300px;"></canvas>
                </div>
                <div class="bg-white p-6 shadow-lg rounded-lg flex flex-col items-center">
                    <h3 class="text-xl font-semibold text-blue-700 mb-4">Appointment Status</h3>
                    <canvas id="appointmentStatusChart" style="max-width: 400px; max-height: 300px;"></canvas>
                </div>
                <div class="bg-white p-6 shadow-lg rounded-lg flex flex-col items-center">
                    <h3 class="text-xl font-semibold text-blue-700 mb-4">Appointments Per Month</h3>
                    <canvas id="monthlyAppointmentsChart" style="max-width: 400px; max-height: 300px;"></canvas>
                </div>
                <div class="bg-white p-6 shadow-lg rounded-lg flex flex-col items-center">
                    <h3 class="text-xl font-semibold text-blue-700 mb-4">Most Requested Services</h3>
                    <canvas id="serviceChart" style="max-width: 400px; max-height: 300px;"></canvas>
                </div>
                <div class="bg-white p-6 shadow-lg rounded-lg flex flex-col items-center col-span-2">
                    <h3 class="text-xl font-semibold text-blue-700 mb-4">Doctor Availability</h3>
                    <canvas id="doctorAvailabilityChart" style="max-width: 500px; max-height: 350px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // User Breakdown Chart
        new Chart(document.getElementById("userBreakdownChart"), {
            type: "pie",
            data: {
                labels: ["Seniors", "Doctors"],
                datasets: [{
                    data: [<?= $seniorCount ?>, <?= $doctorCount ?>],
                    backgroundColor: ["#1E40AF", "#60A5FA"]
                }]
            }
        });

        // Appointment Status Chart
        new Chart(document.getElementById("appointmentStatusChart"), {
            type: "doughnut",
            data: {
                labels: ["Pending", "Accepted", "Declined"],
                datasets: [{
                    data: [<?= $pendingAppointments ?>, <?= $acceptedAppointments ?>, <?= $declinedAppointments ?>],
                    backgroundColor: ["#FBBF24", "#10B981", "#EF4444"]
                }]
            }
        });

        // Monthly Appointments Chart
        new Chart(document.getElementById("monthlyAppointmentsChart"), {
            type: "bar",
            data: {
                labels: [<?php foreach ($monthlyAppointments as $m) echo '"' . date("F", mktime(0, 0, 0, $m['month'], 1)) . '",'; ?>],
                datasets: [{
                    label: "Appointments",
                    data: [<?php foreach ($monthlyAppointments as $m) echo $m['count'] . ','; ?>],
                    backgroundColor: "#2563EB"
                }]
            }
        });

        // Most Requested Services Chart
        new Chart(document.getElementById("serviceChart"), {
            type: "bar",
            data: {
                labels: [<?php foreach ($requestedServices as $s) echo '"' . $s['service_name'] . '",'; ?>],
                datasets: [{
                    label: "Requests",
                    data: [<?php foreach ($requestedServices as $s) echo $s['count'] . ','; ?>],
                    backgroundColor: "#9333EA"
                }]
            }
        });

        // Doctor Availability Chart
        new Chart(document.getElementById("doctorAvailabilityChart"), {
            type: "bar",
            data: {
                labels: ["Available", "Unavailable"],
                datasets: [{
                    label: "Doctors",
                    data: [<?= $availableDoctors ?>, <?= $unavailableDoctors ?>],
                    backgroundColor: ["#34D399", "#F87171"]
                }]
            }
        });
    </script>

</body>
</html>
