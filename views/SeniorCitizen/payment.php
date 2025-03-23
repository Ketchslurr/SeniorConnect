<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include '../../config.php'; // Database connection

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

// Capture appointment details from URL
$seniorId = $_SESSION['userId']; // Assuming the logged-in user is a senior
$professionalId = $_GET['professionalId'] ?? null;
$service = $_GET['service'] ?? null;
$date = $_GET['date'] ?? null;
$time = $_GET['time'] ?? null;

if (!$professionalId || !$service || !$date || !$time) {
    echo "<script>alert('Invalid payment request.'); window.location.href='appointments.php';</script>";
    exit();
}

// Fetch doctor's name and service price
$stmt = $pdo->prepare("SELECT CONCAT(hp.fName, ' ', hp.lName) AS doctor_name, s.price, s.serviceId 
                       FROM healthcareprofessional hp 
                       JOIN services s ON s.professionalId = hp.professionalId 
                       WHERE hp.professionalId = :professionalId AND s.service_name = :service");
$stmt->execute([':professionalId' => $professionalId, ':service' => $service]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

$doctorName = $doctor['doctor_name'] ?? "Unknown Doctor";
$totalAmount = $doctor['price'] ?? "0.00";
$serviceId = $doctor['serviceId'] ?? null;

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
                $stmt = $pdo->prepare("INSERT INTO payments (seniorId, serviceId, amount, status, receipt, paymentDate) 
                                       VALUES (:seniorId, :serviceId, :totalAmount, 'unverified', :payment_proof, NOW())");
                $stmt->execute([
                    ':seniorId' => $seniorId,
                    ':serviceId' => $serviceId,
                    ':totalAmount' => $totalAmount,
                    ':payment_proof' => $fileName
                ]);
                
                $uploadSuccess = true;
            } catch (PDOException $e) {
                echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
            }
        } else {
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
        function closeModal() {
            document.getElementById('successModal').classList.add('hidden');
        }

        function showSuccessModal() {
            document.getElementById('successModal').classList.remove('hidden');
            setTimeout(() => {
                window.location.href = 'seniorCitizenDashboard.php';
            }, 10000);
        }
    </script>
</head>
<body class="bg-gray-100 ">

    <!-- Sidebar -->
    <?php include '../../includes/topbar.php'; ?>
    
    <div class="flex">
        <!-- Topbar -->
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>


        <div class="flex-1 p-6">
        <div class="flex-grow flex justify-center items-center p-4">
        <div class="max-w-md bg-white p-6 rounded-lg shadow-md text-center">
            <h2 class="text-2xl font-bold mb-4">Pay via GCash</h2>

            <p class="text-lg font-semibold">
                GCash Number: <span class="text-blue-500"><?= htmlspecialchars($gcashNumber); ?></span>
            </p>

            <div class="flex justify-center">
                <img src="<?= htmlspecialchars($qrCodePath); ?>" alt="GCash QR Code" class="my-4 w-48 h-48 rounded-lg">
            </div>

            <p class="text-gray-600 mb-4">Scan the QR code using GCash to send your payment.</p>

            <h3 class="text-xl font-semibold mt-4">Appointment Details</h3>
            <p><strong>Doctor:</strong> <?= htmlspecialchars($doctorName); ?></p>
            <p><strong>Service:</strong> <?= htmlspecialchars($service); ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($date); ?></p>
            <p><strong>Time:</strong> <?= htmlspecialchars($time); ?></p>
            <p class="text-xl font-semibold mt-2">
                Total Amount: <span class="text-green-600">₱<?= htmlspecialchars(number_format($totalAmount, 2)); ?></span>
            </p>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-4 mt-4">
                <label class="block">
                    <span class="text-gray-700">Upload Payment Proof:</span>
                    <input type="file" name="payment_proof" required class="block w-full border p-2 rounded">
                </label>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Upload Proof
                </button>
            </form>
        </div>
    </div>
    </div>

    <!-- ✅ Success Modal -->
    <div id="successModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <h2 class="text-xl font-bold text-blue-600">Payment Proof Uploaded Successfully!</h2>
            <p class="text-gray-700 mt-2">Please wait for verification.</p>
            <div class="mt-4">
                <button onclick="window.location.href='seniorCitizenDashboard.php'" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Go to Dashboard
                </button>
                <button onclick="closeModal()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-700">
                    Close
                </button>
            </div>
        </div>
    </div>

</body>
</html>
