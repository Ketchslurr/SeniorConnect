<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

// Get the selected professionalId from the URL
$professionalId = isset($_GET['professionalId']) ? intval($_GET['professionalId']) : 0;
if ($professionalId === 0) {
    die("Invalid doctor selection.");
}

// Fetch doctor details
$sql = "SELECT fname, lname, specialization, profile_picture FROM healthcareprofessional WHERE professionalId = :professionalId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['professionalId' => $professionalId]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    die("Doctor not found.");
}
// Fetch offered services
$sqlServices = "SELECT s.service_name, s.price 
                FROM services s
                INNER JOIN available_doctors ad ON s.professionalId = ad.professionalId
                WHERE s.professionalId = :professionalId";
$stmtServices = $pdo->prepare($sqlServices);
$stmtServices->execute(['professionalId' => $professionalId]);
$services = $stmtServices->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>

    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>
        
        <main class="flex-1 p-6">
            <a href="telehealth.php" class="text-blue-600 hover:underline">&larr; Back</a>
            <h2 class="text-3xl font-bold text-center mb-6">Book an Appointment</h2>

            <div class="bg-white p-6 rounded-lg shadow-md max-w-3xl mx-auto">
                <div class="flex items-center space-x-4">
                    <img src="<?= !empty($doctor['profile_picture']) ? 'data:image/jpeg;base64,' . base64_encode($doctor['profile_picture']) : '../../assets/Images/default-profile.png' ?>" class="w-24 h-24 rounded-full border" />
                    <div>
                        <p class="text-xl font-semibold"><?= htmlspecialchars($doctor['fname'] . ' ' . $doctor['lname']) ?></p>
                        <p class="text-gray-600"><?= htmlspecialchars($doctor['specialization']) ?></p>
                    </div>
                </div>
                
                <h3 class="text-xl font-bold mt-6">Available Services</h3>
                <ul class="mt-4 space-y-3">
                    <?php foreach ($services as $service): ?>
                        <li class="flex justify-between p-3 bg-gray-50 rounded-lg shadow">
                            <span><?= htmlspecialchars($service['service_name']) ?></span>
                            <span class="font-semibold text-blue-600">$<?= htmlspecialchars($service['price']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <button class="mt-6 w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition" onclick="window.location.href='appointmentBookingPage.php?professionalId=<?= $professionalId ?>'">Proceed</button>
            </div>
        </main>
    </div>
</body>
</html>
