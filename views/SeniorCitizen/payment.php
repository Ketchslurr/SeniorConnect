<?php
session_start();
include '../../config.php'; // Database connection

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

// Capture appointment details from URL
$professionalId = $_GET['professionalId'] ?? null;
$service = $_GET['service'] ?? null;
$date = $_GET['date'] ?? null;
$time = $_GET['time'] ?? null;

if (!$professionalId || !$service || !$date || !$time) {
    echo "<script>alert('Invalid payment request.'); window.location.href='appointments.php';</script>";
    exit();
}

$gcashNumber = "09912166297"; // Replace with actual GCash number
$qrCodePath = "../../assets/qr.jpg"; // Replace with actual QR image path

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
            $userId = $_SESSION['userId'];

            try {
                $stmt = $pdo->prepare("INSERT INTO payments (user_id, professional_id, service, appointment_date, appointment_time, payment_proof, status) 
                                       VALUES (:userId, :professionalId, :service, :appointment_date, :appointment_time, :payment_proof, 'Pending')");
                $stmt->execute([
                    ':userId' => $userId,
                    ':professionalId' => $professionalId,
                    ':service' => $service,
                    ':appointment_date' => $date,
                    ':appointment_time' => $time,
                    ':payment_proof' => $fileName
                ]);

                // Redirect with a success flag in the URL
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=true&professionalId=$professionalId&service=$service&date=$date&time=$time");
                exit();
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

        // Show the modal if success is detected in the URL
        window.onload = function () {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                document.getElementById('successModal').classList.remove('hidden');
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="max-w-md mx-auto mt-10 bg-white p-6 rounded-lg shadow-md text-center">
        <h2 class="text-2xl font-bold mb-4">Pay via GCash</h2>
        
        <p class="text-lg font-semibold">GCash Number: <span class="text-blue-500"><?= htmlspecialchars($gcashNumber); ?></span></p>
        <img src="<?= htmlspecialchars($qrCodePath); ?>" alt="GCash QR Code" class="mx-auto my-4 w-48 h-48 rounded-lg">
        
        <p class="text-gray-600 mb-4">Scan the QR code using GCash to send your payment.</p>

        <h3 class="text-xl font-semibold mt-4">Appointment Details</h3>
        <p><strong>Service:</strong> <?= htmlspecialchars($service); ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($date); ?></p>
        <p><strong>Time:</strong> <?= htmlspecialchars($time); ?></p>

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

    <!-- Success Modal -->
    <div id="successModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <h2 class="text-xl font-bold text-green-600">Payment Proof Uploaded Successfully!</h2>
            <p class="text-gray-700 mt-2">Please wait for verification.</p>
            <div class="mt-4">
                <button onclick="window.location.href='dashboard.php'" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
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
