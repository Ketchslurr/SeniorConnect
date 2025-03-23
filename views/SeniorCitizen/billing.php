<?php
include '../../config.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$seniorId = $_SESSION['userId'];

// Fetch billing transactions
$billingStmt = $pdo->prepare("
    SELECT p.*, s.service_name 
    FROM payments p
    JOIN services s ON p.serviceId = s.serviceId
    WHERE p.seniorId = :seniorId
    ORDER BY p.paymentDate DESC
");
$billingStmt->execute(['seniorId' => $seniorId]);
$transactions = $billingStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing & Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <!-- ✅ Include Topbar -->
    <?php include '../../includes/topbar.php'; ?>

    <div class="flex">
        <!-- ✅ Include Sidebar -->
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>

        <div class="flex-1 p-6">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6">Billing & Payments</h1>

            <div class="bg-white shadow-md rounded-lg p-6">
                <?php if (count($transactions) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-200">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="border p-3 text-left">Service</th>
                                    <th class="border p-3 text-left">Amount</th>
                                    <th class="border p-3 text-left">Status</th>
                                    <th class="border p-3 text-left">Receipt</th>
                                    <th class="border p-3 text-left">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr class="border-b">
                                        <td class="p-3"><?php echo htmlspecialchars($transaction['service_name']); ?></td>
                                        <td class="p-3">₱<?php echo number_format($transaction['amount'], 2); ?></td>
                                        <td class="p-3">
                                            <?php if ($transaction['status'] == 'Pending'): ?>
                                                <a href="payment.php?paymentId=<?php echo $transaction['paymentId']; ?>" 
                                                   class="text-yellow-500 font-semibold hover:underline">
                                                    Pay Now
                                                </a>
                                            <?php else: ?>
                                                <span class="text-green-500 font-semibold">✅ Paid</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-3">
                                            <?php if (!empty($transaction['receipt'])): ?>
                                                <a href="../../assets/uploads/payments/<?php echo $transaction['receipt']; ?>" 
                                                   target="_blank" 
                                                   class="text-blue-500 underline">
                                                   View Receipt
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-500">No receipt uploaded</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-3 text-gray-500">
                                            <?php echo date("M d, Y", strtotime($transaction['paymentDate'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">No billing transactions found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
