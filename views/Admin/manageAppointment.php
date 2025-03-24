<?php
session_start();
if (!isset($_SESSION["adminId"])) {
    header("Location: login.php");
    exit();
}

require '../../config.php';

// Fetch all appointments with senior & doctor names
$stmt = $pdo->query("
    SELECT a.appointmentId, 
           s.fName AS senior_fname, s.lName AS senior_lname,
           d.fName AS doctor_fname, d.lName AS doctor_lname,
           a.service_name, a.appointment_date, a.appointment_time, 
           a.appointment_status, a.doctor_response, a.meeting_link
    FROM appointment a
    JOIN seniorcitizen s ON a.seniorId = s.seniorId
    JOIN healthcareprofessional d ON a.professionalId = d.professionalId
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <?php include '../../includes/adminTopbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/adminSidebar.php'; ?>

        <div class="flex-1 p-6">
            <h2 class="text-2xl font-bold text-blue-600 mb-4">Manage Appointments</h2>

            <table class="w-full bg-white shadow-md rounded-lg">
                <thead class="bg-blue-500 text-white">
                    <tr>
                        <th class="p-3">Senior</th>
                        <th class="p-3">Doctor</th>
                        <th class="p-3">Service</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">Time</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Doctor Response</th>
                        <th class="p-3">Meeting Link</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr class="border-b">
                            <td class="p-3"><?= htmlspecialchars($appointment['senior_fname'] . " " . $appointment['senior_lname']); ?></td>
                            <td class="p-3"><?= htmlspecialchars($appointment['doctor_fname'] . " " . $appointment['doctor_lname']); ?></td>
                            <td class="p-3"><?= htmlspecialchars($appointment['service_name']); ?></td>
                            <td class="p-3"><?= htmlspecialchars($appointment['appointment_date']); ?></td>
                            <td class="p-3"><?= htmlspecialchars($appointment['appointment_time']); ?></td>
                            <td class="p-3 font-semibold <?= $appointment['appointment_status'] == 'Pending' ? 'text-yellow-600' : ($appointment['appointment_status'] == 'Accepted' ? 'text-green-600' : 'text-red-600') ?>">
                                <?= htmlspecialchars($appointment['appointment_status']); ?>
                            </td>
                            <td class="p-3"><?= htmlspecialchars($appointment['doctor_response'] ?: 'N/A'); ?></td>
                            <td class="p-3">
                                <?php if ($appointment['meeting_link']): ?>
                                    <a href="<?= htmlspecialchars($appointment['meeting_link']); ?>" target="_blank" class="text-blue-600 underline">Join</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td class="p-3">
                                <form action="updateAppointment.php" method="POST" class="inline-block">
                                    <input type="hidden" name="appointmentId" value="<?= $appointment['appointmentId']; ?>">
                                    <button type="submit" name="action" value="accept" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-green-700">Accept</button>
                                    <button type="submit" name="action" value="decline" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-700">Decline</button>
                                </form>
                                <a href="deleteAppointment.php?id=<?= $appointment['appointmentId']; ?>" class="bg-black text-white px-3 py-1 rounded hover:bg-gray-700">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
