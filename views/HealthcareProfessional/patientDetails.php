<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['seniorId'])) {
    echo "Invalid request.";
    exit();
}

$seniorId = $_GET['seniorId'];

// Fetch patient details
$sql = "SELECT * FROM seniorcitizen WHERE seniorId = :seniorId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['seniorId' => $seniorId]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch medical records
$sqlRecords = "SELECT * FROM medical_records WHERE seniorId = :seniorId ORDER BY uploaded_at DESC";
$stmtRecords = $pdo->prepare($sqlRecords);
$stmtRecords->execute(['seniorId' => $seniorId]);
$medicalRecords = $stmtRecords->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/healthcareProfessionalSidebar.php'; ?>

        <main class="flex-1 p-6">
            <h2 class="text-3xl font-bold text-center mb-6">Patient Details</h2>
            
            <div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto">
                <h3 class="text-2xl mb-4">
                    <?= htmlspecialchars($patient['fName'] . ' ' . $patient['lName']) ?>
                </h3>
                <p><strong>Age:</strong> <?= htmlspecialchars($patient['age']) ?></p>
                <!-- <p><strong>Contact:</strong> <?= htmlspecialchars($patient['contact']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($patient['address']) ?></p> -->
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto mt-6">
                <h3 class="text-2xl font-bold mb-4">Medical Records</h3>
                <?php if (!empty($medicalRecords)): ?>
                    <ul class="list-disc pl-6">
                        <?php foreach ($medicalRecords as $record): ?>
                            <li class="mb-2">
                                <a href="../../uploads/medical_records/<?= htmlspecialchars($record['file_name']) ?>" 
                                   class="text-blue-600 underline" target="_blank">
                                    <?= htmlspecialchars($record['file_name']) ?>
                                </a> 
                                <span class="text-gray-500">(Uploaded: <?= htmlspecialchars($record['uploaded_at']) ?>)</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-600">No medical records available.</p>
                <?php endif; ?>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto mt-6">
                <h3 class="text-2xl font-bold mb-4">Patient Vitals</h3>
                <p class="text-lg"><strong>Blood Pressure:</strong> <?= htmlspecialchars($patient['blood_pressure'] ?? 'Not Set') ?></p>
                <p class="text-lg"><strong>Heart Rate:</strong> <?= htmlspecialchars($patient['heart_rate'] ?? 'Not Set') ?> BPM</p>
                <p class="text-lg"><strong>Oxygen Level:</strong> <?= htmlspecialchars($patient['oxygen_level'] ?? 'Not Set') ?>%</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto mt-6 text-center">
                <h3 class="text-2xl font-semibold mb-4">Request Vitals</h3>
                <form action="requestVitals.php" method="POST">
                    <input type="hidden" name="seniorId" value="<?= $seniorId ?>">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Request Blood Pressure & Vitals
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
