<?php
include '../../config.php';
session_start();

// Redirect if not logged in or not a senior
if (!isset($_SESSION['userId']) || $_SESSION['roleId'] != 2) {
    header("Location: ../../login.php");
    exit();
}

$seniorId = $_SESSION['seniorId'] ?? null;

// Get prescriptions tied to this senior
$sql = "
    SELECT p.prescriptionId, p.prescription_text, p.instructions, p.created_at,
           h.fname AS doctor_fname, h.lname AS doctor_lname
    FROM prescriptions p
    JOIN appointment a ON p.appointmentId = a.appointmentId
    JOIN healthcareprofessional h ON a.professionalId = h.professionalId
    WHERE a.seniorId = :seniorId AND p.deleted_at IS NULL
    ORDER BY p.created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['seniorId' => $seniorId]);
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Prescriptions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include '../../includes/topbar.php'; ?>
<div class="flex">
    <?php include '../../includes/seniorSidebar.php'; ?>

    <main class="flex-1 p-6">
        <h2 class="text-2xl font-bold text-center mb-6">My Prescriptions</h2>

        <div class="bg-white p-6 shadow-md rounded-lg">
            <?php if (count($prescriptions) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full border border-gray-200">
                        <thead class="bg-purple-600 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Doctor</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Date</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Prescription</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold">Instructions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prescriptions as $rx): ?>
                                <tr class="border-t">
                                    <td class="px-6 py-4 text-sm"><?= htmlspecialchars($rx['doctor_fname'] . ' ' . $rx['doctor_lname']) ?></td>
                                    <td class="px-6 py-4 text-sm"><?= htmlspecialchars(date("F j, Y - g:i A", strtotime($rx['created_at']))) ?></td>
                                    <td class="px-6 py-4 text-sm"><?= nl2br(htmlspecialchars($rx['prescription_text'])) ?></td>
                                    <td class="px-6 py-4 text-sm"><?= nl2br(htmlspecialchars($rx['instructions'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600 text-center">You don't have any prescriptions yet.</p>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
