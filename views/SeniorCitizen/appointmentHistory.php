<?php
session_start();
include '../../config.php';

// Ensure the user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: ../views/login.php");
    exit();
}

$seniorId = $_SESSION['seniorId'];

$stmt = $pdo->prepare("SELECT a.appointment_date, a.appointment_time, a.service_name, a.appointment_status, 
                              a.doctor_response, a.meeting_link, p.fname AS doctor_fname, p.lname AS doctor_lname
                       FROM appointment a
                       JOIN healthcareprofessional p ON a.professionalId = p.professionalId
                       WHERE a.seniorId = :seniorId
                       ORDER BY a.appointment_date DESC");

$stmt->execute(['seniorId' => $seniorId]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment History</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="fixed top-0 left-0 w-full bg-blue-600 text-white shadow-md z-50 py-4">
        <div class="container mx-auto flex justify-between items-center px-6">
            <h2 class="text-2xl font-bold"><a href="../index.php">Senior <span class="text-cyan-300">Connect</span></a></h2>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="../index.php" class="hover:underline">Home</a></li>
                    <li><a href="services.php" class="hover:underline">Services</a></li>
                    <li><a href="profile.php" class="hover:underline">Profile</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container mx-auto mt-20 px-6">
    <div class="mb-4">
            <button onclick="window.history.back()" class="bg-blue-500 text-white px-4 py-2 rounded-md shadow-md hover:bg-gray-700">
                ← Back
            </button>
        </div>
        <h2 class="text-3xl font-bold text-center mb-6">Appointment History</h2>
        <div class="bg-white p-6 rounded-lg shadow-lg overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-blue-500 text-white">
                        <th class="p-3 border">Date</th>
                        <th class="p-3 border">Time</th>
                        <th class="p-3 border">Service</th>
                        <th class="p-3 border">Doctor</th>
                        <th class="p-3 border">Status</th>
                        <th class="p-3 border">Doctor's Response</th>
                        <th class="p-3 border">Meeting Link</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($appointments) > 0): ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr class="text-center border">
                                <td class="p-3 border"><?php echo date("F j, Y", strtotime($appointment['appointment_date'])); ?></td>
                                <td class="p-3 border"><?php echo date("g:i A", strtotime($appointment['appointment_time'])); ?></td>
                                <td class="p-3 border"><?php echo htmlspecialchars($appointment['service_name']); ?></td>
                                <td class="p-3 border"><?php echo htmlspecialchars($appointment['doctor_fname'] . ' ' . $appointment['doctor_lname']); ?></td>
                                <td class="p-3 border">
                                    <?php if ($appointment['appointment_status'] == 'Confirmed'): ?>
                                        <span class="text-green-500 font-semibold">Confirmed</span>
                                    <?php elseif ($appointment['appointment_status'] == 'Pending'): ?>
                                        <span class="text-yellow-500 font-semibold">Pending</span>
                                    <?php else: ?>
                                        <span class="text-red-500 font-semibold">Canceled</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 border"><?php echo htmlspecialchars($appointment['doctor_response']?? ''); ?></td>
                                <td class="p-3 border">
                                    <?php if (!empty($appointment['meeting_link'])): ?>
                                        <a href="<?php echo htmlspecialchars($appointment['meeting_link']); ?>" 
                                           target="_blank" 
                                           class="text-blue-500 underline">
                                           Join Meeting
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-500">No Link</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="p-3 text-center text-gray-500">No appointment history found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
