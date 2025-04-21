<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
define('APP_SECRET_KEY', '8c9f1a2d59b1c0e5aa33d8e5ef3b7c19'); 
include '../../config.php'; // Database connection

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

// Capture appointment details from URL
$seniorId = $_SESSION['seniorId']; // Assuming the logged-in user is a senior
$professionalId = $_GET['professionalId'] ?? null;
$date = $_GET['date'] ?? null;
$time = $_GET['time'] ?? null;

if (!$professionalId || !$date || !$time) {
    echo "<script>alert('Invalid payment request.'); window.location.href='appointments.php';</script>";
    exit();
}
// Encrypt the paymentId
function encryptPaymentId($id, $key) {
    $cipher = "AES-128-CTR";
    $iv = '1234567891011121'; // 16 bytes IV (keep it constant or dynamic)
    return bin2hex(openssl_encrypt($id, $cipher, $key, 0, $iv));
}

function decryptPaymentId($encryptedId, $key) {
    $cipher = "AES-128-CTR";
    $iv = '1234567891011121';
    return openssl_decrypt(hex2bin($encryptedId), $cipher, $key, 0, $iv);
}


// Fetch doctor's name and consultation fee
$stmt = $pdo->prepare("SELECT CONCAT(fName, ' ', lName) AS doctor_name, consultationFee 
                       FROM healthcareprofessional 
                       WHERE professionalId = :professionalId");
$stmt->execute([':professionalId' => $professionalId]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

$doctorName = $doctor['doctor_name'] ?? "Unknown Doctor";
$totalAmount = $doctor['consultationFee'] ?? "0.00";

$gcashNumber = "09912166297"; 
$qrCodePath = "../../assets/qr.jpg"; 

$uploadSuccess = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['payment_proof'])) {
    $uploadDir = "../../assets/uploads/payments/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["payment_proof"]["name"]);
    $targetFilePath = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $targetFilePath)) {
            try {
                // Insert payment record
                $stmt = $pdo->prepare("INSERT INTO payments (seniorId, professionalId, amount, status, receipt, paymentDate) 
                                       VALUES (:seniorId, :professionalId, :totalAmount, 'unverified', :payment_proof, NOW())");
                $stmt->execute([
                    ':seniorId' => $seniorId,
                    ':professionalId' => $professionalId,
                    ':totalAmount' => $totalAmount,
                    ':payment_proof' => $fileName
                ]);

                $paymentId = $pdo->lastInsertId();
                $encryptedId = encryptPaymentId($paymentId, APP_SECRET_KEY);

                // Send notification
                $notifSql = "INSERT INTO notifications (seniorId, message, link, created_at) 
                             VALUES (:seniorId, :message, :link, NOW())";
                $notifStmt = $pdo->prepare($notifSql);
                $notifStmt->execute([
                    'seniorId' => $seniorId,
                    'message' => 'Your appointment is now pending verification. Please wait for confirmation.',
                    'link' => 'notifications.php'
                ]);
        
                echo "<script>localStorage.setItem('showSuccessModal', 'true');</script>";
                $_SESSION['invoice_url'] = "transactionIdEncryption.php?invoice=" . urlencode($encryptedId);
                $uploadSuccess = true;
            } catch (PDOException $e) {
                echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
            }
        }else {
            echo "<script>alert('File upload failed, please try again.');</script>";
        }
    } else {
        echo "<script>alert('Only JPG, JPEG, PNG, and GIF files are allowed.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GCash Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (localStorage.getItem("showSuccessModal") === "true") {
                showSuccessModal();
                localStorage.removeItem("showSuccessModal"); // Remove flag after showing modal
            }
        });

        function showSuccessModal() {
            document.getElementById("successModal").classList.remove("hidden");
            setTimeout(() => {
                window.location.href = "seniorCitizenDashboard.php";
            }, 10000);
        }

        function closeModal() {
            document.getElementById("successModal").classList.add("hidden");
        }
    </script>
</head>
<body class="bg-gray-100">

    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>
        <div class="flex-1 p-6">
            <div class="flex-grow flex justify-center items-center p-4">
                <div class="max-w-md bg-white p-6 rounded-lg shadow-md text-center">
                    <h2 class="text-2xl font-bold mb-4">Pay via GCash</h2>
                    <p class="text-lg font-semibold">GCash Number: <span class="text-blue-500"><?= htmlspecialchars($gcashNumber); ?></span></p>
                    <div class="flex justify-center">
                        <img src="<?= htmlspecialchars($qrCodePath); ?>" alt="GCash QR Code" class="my-4 w-48 h-48 rounded-lg">
                    </div>
                    <p class="text-gray-600 mb-4">Scan the QR code using GCash to send your payment.</p>
                    <h3 class="text-xl font-semibold mt-4">Appointment Details</h3>
                    <p><strong>Doctor:</strong> <?= htmlspecialchars($doctorName); ?></p>
                    <p><strong>Date:</strong> <?= htmlspecialchars($date); ?></p>
                    <p><strong>Time:</strong> <?= htmlspecialchars($time); ?></p>
                    <p class="text-xl font-semibold mt-2">Total Amount: <span class="text-green-600">₱<?= htmlspecialchars(number_format($totalAmount, 2)); ?></span></p>
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4 mt-4">
                        <label class="block">
                            <span class="text-gray-700">Upload Payment Proof:</span>
                            <input type="file" name="payment_proof" required class="block w-full border p-2 rounded">
                        </label>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Upload Proof</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="successModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <h2 class="text-xl font-bold text-blue-600">Payment Proof Uploaded Successfully!</h2>
            <p class="text-gray-700 mt-2">Please wait for verification.</p>
            <a href="<?= $_SESSION['invoice_url'] ?? '#' ?>" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700">View Invoice</a>
            <div class="mt-4">
                <button onclick="window.location.href='seniorCitizenDashboard.php'" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Go to Dashboard</button>
                <button onclick="closeModal()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-700">Close</button>
            </div>
        </div>
    </div>

</body>
</html>
