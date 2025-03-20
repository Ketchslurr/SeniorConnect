<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId']) || !isset($_SESSION['professionalId'])) {
    header("Location: login.php");
    exit();
}

$professionalId = $_SESSION['professionalId'];

// Fetch patients assigned to the logged-in doctor
$sql = "SELECT DISTINCT s.seniorId, s.fname, s.lname, s.profile_picture
        FROM appointment a
        JOIN seniorcitizen s ON a.seniorId = s.seniorId
        WHERE a.professionalId = :professionalId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['professionalId' => $professionalId]);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch consultation statistics for the doctor
$stats_sql = "SELECT s.fname, s.lname, COUNT(a.appointmentId) as consult_count
              FROM appointment a
              JOIN seniorcitizen s ON a.seniorId = s.seniorId
              WHERE a.professionalId = :professionalId
              GROUP BY s.seniorId
              ORDER BY consult_count DESC";
$stats_stmt = $pdo->prepare($stats_sql);
$stats_stmt->execute(['professionalId' => $professionalId]);
$statistics = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/healthcareProfessionalSidebar.php'; ?>

        <main class="flex-1 p-6">
            <h2 class="text-3xl font-bold text-center mb-6">Your Patients</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($patients as $patient): ?>
                    <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow flex flex-col items-center text-center cursor-pointer" 
                         onclick="window.location.href='patientDetails.php?seniorId=<?= $patient['seniorId'] ?>'">
                        <?php if (!empty($patient['profile_picture'])): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($patient['profile_picture']) ?>" class="w-24 h-24 rounded-full border mb-4" />
                        <?php else: ?>
                            <img src="../../assets/Images/default-user.png" class="w-24 h-24 rounded-full border mb-4" />
                        <?php endif; ?>
                        <h3 class="text-xl font-semibold"> <?= htmlspecialchars($patient['fname'] . ' ' . $patient['lname']) ?> </h3>
                        <button class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            View Details
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2 class="text-3xl font-bold text-center mt-12">Consultation Statistics</h2>
            <div class="bg-white p-6 rounded-lg shadow-md mt-6">
                <canvas id="consultChart"></canvas>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var ctx = document.getElementById('consultChart').getContext('2d');
            var chartData = {
                labels: <?= json_encode(array_column($statistics, 'fname')) ?>,
                datasets: [{
                    label: 'Number of Consultations',
                    data: <?= json_encode(array_column($statistics, 'consult_count')) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            };

            new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
    </script>
</body>
</html>
