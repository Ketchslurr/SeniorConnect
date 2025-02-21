<?php
include '../../config.php';
session_start();


if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}
// Fetch available doctors using JOIN
$sql = "SELECT hp.fname, hp.lname, hp.specialization, hp.profile_picture FROM available_doctors ad JOIN healthcareprofessional hp ON ad.professionalId = hp.userId WHERE ad.is_available = 1;";

$stmt = $pdo->query($sql);
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Services</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>
        
        <main class="flex-1 p-6">
            <h2 class="text-3xl font-bold text-center mb-6">Available Doctors</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (!empty($doctors)): ?>
                    <?php foreach ($doctors as $doctor): ?>
                        <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow flex flex-col items-center text-center">
                            <?php if (!empty($doctor['profile_picture'])): ?>
                                <img src="data:image/jpeg;base64,<?= base64_encode($doctor['profile_picture']) ?>" class="w-24 h-24 rounded-full border mb-4" />
                            <?php else: ?>
                                <img src="../../assets/Images/default-doctor.png" class="w-24 h-24 rounded-full border mb-4" />
                            <?php endif; ?>
                            <h3 class="text-xl font-semibold"> <?= htmlspecialchars($doctor['fname'] . ' ' . $doctor['lname']) ?> </h3>
                            <p class="text-gray-600 mb-4"> <?= htmlspecialchars($doctor['specialization']) ?> </p>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition" onclick="window.location.href='bookAppointment.php'">
                                Book an Appointment
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-600 col-span-full text-center">No available services at the moment.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
