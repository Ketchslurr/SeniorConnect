<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId']) || !isset($_SESSION['professionalId'])) {
    header("Location: login.php");
    exit();
}

$professionalId = $_SESSION['professionalId'];
$currentDate = date('Y-m-d');

// Fetch confirmed appointments
$sql = "SELECT ha.*, sc.*
        FROM appointment ha
        JOIN seniorcitizen sc ON ha.seniorId = sc.seniorId
        WHERE ha.professionalId = :professionalId 
        AND ha.appointment_date >= :currentDate 
        AND ha.appointment_status = 'Confirmed' 
        ORDER BY ha.appointment_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['professionalId' => $professionalId, 'currentDate' => $currentDate]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meetings</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/healthcareProfessionalSidebar.php'; ?>
        <main class="flex-1 p-6">
            <h2 class="text-3xl font-bold text-center mb-6">Scheduled Meetings</h2>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-2xl font-bold mb-4">Confirmed Appointments</h3>
                <div class="space-y-4">
                    <?php if (!empty($appointments)) { 
                        foreach ($appointments as $row) { ?>
                            <div class="border-l-4 border-blue-500 bg-blue-100 p-4 hover:bg-blue-200 transition">
                                <p><strong>Senior Name:</strong> <?= htmlspecialchars($row['fName']) ?></p>
                                <p><strong>Age:</strong> <?= htmlspecialchars($row['age']) ?></p>
                                <p><strong>Gender:</strong> <?= htmlspecialchars($row['gender']) ?></p>
                                <p><strong>Date:</strong> <?= htmlspecialchars($row['appointment_date']) ?></p>
                                <button onclick="startMeeting(<?= htmlspecialchars($row['appointmentId']) ?>)" 
                                    class="mt-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                                    Start Google Meet
                                </button>
                            </div>
                        <?php } 
                    } else { ?>
                        <div class="border-l-4 border-gray-500 bg-gray-100 p-4">
                            <p>No confirmed appointments.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>
    <script>
        function startMeeting(appointmentId) {
    fetch('startMeeting.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ appointmentId: appointmentId })
    })
    .then(response => response.text()) // Get raw response instead of JSON
    .then(data => {
        console.log("Raw response:", data); // Log raw response
        try {
            let jsonData = JSON.parse(data); // Parse JSON
            if (jsonData.success) {
                alert("Meeting link sent to the senior");
                window.open(jsonData.meet_link, '_blank');
            } else {
                alert("Error: " + jsonData.error);
            }
        } catch (error) {
            console.error("Invalid JSON:", data);
        }
    })
    .catch(error => console.error('Error:', error));
}


    </script>
</body>
</html>
