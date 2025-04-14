<?php
include '../../config.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$seniorId = $_SESSION['seniorId'];

// Fetch billing transactions
$billingStmt = $pdo->prepare("
    SELECT p.*
    FROM payments p
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
                                    <th class="border p-3 text-left">Payment ID</th>
                                    <th class="border p-3 text-left">Amount</th>
                                    <th class="border p-3 text-left">Status</th>
                                    <th class="border p-3 text-left">Receipt</th>
                                    <th class="border p-3 text-left">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $transaction): ?>
                                    <tr class="border-b">
                                        <td class="p-3"><?php echo htmlspecialchars($transaction['paymentId']); ?></td>
                                        <td class="p-3">₱<?php echo number_format($transaction['amount'], 2); ?></td>
                                        <td class="p-3">
                                            <?php if (!empty($transaction['receipt']) && file_exists("../../assets/uploads/payments/" . $transaction['receipt'])): ?>
                                                <span class="text-green-500 font-semibold"> Paid</span>
                                            <?php elseif ($transaction['status'] == 'unverified'): ?>
                                                <a href="payment.php?paymentId=<?php echo $transaction['paymentId']; ?>" 
                                                class="text-yellow-500 font-semibold hover:underline">
                                                    Pay Now
                                                </a>
                                            <?php else: ?>
                                                <span class="text-green-500 font-semibold"> Paid</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-3">
                                            <?php if (!empty($transaction['receipt']) && file_exists("../../assets/uploads/payments/" . $transaction['receipt'])): ?>
                                                <button onclick="openModal('<?php echo $transaction['receipt']; ?>')" 
                                                        class="text-blue-500 underline">
                                                    View Receipt
                                                </button>
                                            <?php else: ?>
                                                <span class="text-red-500">Receipt not found. Please upload again.</span>
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

    <!-- ✅ Receipt Modal -->
    <div id="receiptModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center p-4" onclick="closeModal(event)">
    <div class="bg-white p-6 rounded-lg shadow-lg w-[90vw] max-w-4xl max-h-[90vh] overflow-auto relative" onclick="event.stopPropagation()">
        <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        <h2 class="text-2xl font-semibold mb-4 text-center">Receipt Preview</h2>
        <div class="flex justify-center">
            <img id="receiptImage" src="" alt="Receipt" class="max-w-full max-h-[70vh] rounded-lg shadow-md">
        </div>
    </div>
</div>

<script>
    function openModal(receiptFile) {
        const modal = document.getElementById("receiptModal");
        const receiptImage = document.getElementById("receiptImage");

        if (receiptFile) {
            receiptImage.src = "../../assets/uploads/payments/" + receiptFile;
        } else {
            receiptImage.src = "https://via.placeholder.com/500x300?text=Receipt+Not+Found";
        }

        modal.classList.remove("hidden");
    }

    function closeModal(event = null) {
        if (!event || event.target.id === "receiptModal") {
            document.getElementById("receiptModal").classList.add("hidden");
        }
    }
</script>
</body>
</html>
