<?php
include '../config.php';
session_start();

if (!isset($_SESSION['userId']) || !isset($_GET['appointmentId'])) {
    header("Location: login.php");
    exit();
}

$appointmentId = $_GET['appointmentId'];

// Fetch appointment info
$stmt = $pdo->prepare("SELECT a.*, s.fName, s.lName FROM appointment a 
    JOIN seniorcitizen s ON a.seniorId = s.seniorId 
    WHERE a.appointmentId = ?");
$stmt->execute([$appointmentId]);
$appointment = $stmt->fetch();

if (!$appointment) {
    echo "Appointment not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Prescription + Meeting</title>
    <script src='https://meet.jit.si/external_api.js'></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <div class="w-2/3 p-4">
            <div id="jitsi-container" class="w-full h-full rounded shadow"></div>
        </div>
        <div class="w-1/3 bg-white p-4 overflow-auto shadow-lg">
            <h2 class="text-xl font-bold mb-2">Write Prescription</h2>
            <p class="mb-1"><strong>Patient:</strong> <?= htmlspecialchars($appointment['fName']) ?> <?= htmlspecialchars($appointment['lName']) ?></p>
            <p class="mb-4"><strong>Date:</strong> <?= $appointment['appointment_date'] ?></p>
            <form method="post" action="savePrescription.php">
                <input type="hidden" name="appointmentId" value="<?= $appointmentId ?>">
                <textarea name="prescription" rows="10" class="w-full p-2 border rounded" placeholder="Enter prescription..."></textarea>
                <button type="submit" class="mt-4 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Save Prescription
                </button>
            </form>
        </div>
    </div>

    <script>
        const domain = "meet.jit.si";
        const options = {
            roomName: "SeniorConnect_Appointment_<?= $appointmentId ?>",
            parentNode: document.querySelector('#jitsi-container'),
            width: "100%",
            height: "100%",
            interfaceConfigOverwrite: {
                SHOW_JITSI_WATERMARK: false,
                SHOW_BRAND_WATERMARK: false,
            },
            configOverwrite: {
                disableDeepLinking: true
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
    </script>
</body>
</html>
