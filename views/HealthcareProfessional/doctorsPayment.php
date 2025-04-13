<?php
session_start();
include '../../config.php'; 

if (!isset($_SESSION['userId'])) { 
    header("Location: login.php");
    exit();
}

$professionalId = $_SESSION['professionalId'];

// Fetch only payments with receipts for the logged-in doctor
$stmt = $pdo->prepare("SELECT p.paymentId, CONCAT(sc.fName, ' ', sc.lName) AS senior_name, 
                              hp.consultationFee AS consultation_fee, p.amount, p.status, p.receipt, p.paymentDate 
                       FROM payments p 
                       JOIN seniorcitizen sc ON p.seniorId = sc.seniorId
                       JOIN healthcareprofessional hp ON p.professionalId = hp.professionalId
                       WHERE p.receipt IS NOT NULL 
                         AND p.receipt != '' 
                         AND p.professionalId = ?
                       ORDER BY p.paymentDate DESC");
$stmt->execute([$professionalId]);

$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipts</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/healthcareProfessionalSidebar.php'; ?>

        <div class="flex-1 p-6">
            <h2 class="text-2xl font-bold mb-4">Payment Proofs</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white shadow-md rounded-lg">
                    <thead class="bg-blue-500 text-white">
                        <tr>
                            <th class="py-2 px-4">Senior Name</th>
                            <th class="py-2 px-4">Consultation Fee</th>
                            <!-- <th class="py-2 px-4">Amount</th> -->
                            <th class="py-2 px-4">Status</th>
                            <th class="py-2 px-4">Receipt</th>
                            <th class="py-2 px-4">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr class="border-b">
                                <td class="py-2 px-4"><?= htmlspecialchars($payment['senior_name']); ?></td>
                                <!-- <td class="py-2 px-4"><//?= htmlspecialchars($payment['service_name']); ?></td> RHCM 4/5/2025 commented out as I changed service into consultation--> 
                                <td class="py-2 px-4">₱<?= htmlspecialchars(number_format($payment['consultation_fee'], 2)); ?></td>
                                <!-- <td class="py-2 px-4">₱<//?= htmlspecialchars(number_format($payment['amount'], 2)); ?></td> -->
                                <td class="py-2 px-4 text-<?= $payment['status'] == 'verified' ? 'green' : 'red' ?>-500">
                                    <?= htmlspecialchars(ucfirst($payment['status'])); ?>
                                </td>
                                <td class="py-2 px-4">
                                    <?php if (!empty($payment['receipt'])): ?>
                                        <a href="../../assets/uploads/payments/<?= htmlspecialchars($payment['receipt']); ?>" target="_blank" class="text-blue-500 hover:underline">View</a>
                                    <?php else: ?>
                                        <span class="text-gray-500">No receipt</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 px-4"><?= date('F j, Y, g:i A', strtotime($payment['paymentDate'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</body>
</html>
