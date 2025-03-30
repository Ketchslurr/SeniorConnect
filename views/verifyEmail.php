<?php
session_start();
include '../config.php';

$error = "";
$success = "";

// ✅ Automatic verification via URL
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['code'])) {
    $verificationCode = $_GET['code'];

    // ✅ Check if the verification code exists
    $stmt = $pdo->prepare("SELECT userId FROM user_info WHERE verification_code = ? AND is_verified = 0");
    $stmt->execute([$verificationCode]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // ✅ Mark the user as verified
        $stmt = $pdo->prepare("UPDATE user_info SET is_verified = 1 WHERE userId = ?");
        if ($stmt->execute([$user['userId']])) {
            $_SESSION['message'] = "Your email has been verified! You can now log in.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
    } else {
        $error = "Invalid or expired verification code.";
    }
}

// ✅ Manual code input handling
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['verification_code'])) {
    $verificationCode = trim($_POST['verification_code']);

    if (!empty($verificationCode)) {
        $stmt = $pdo->prepare("SELECT userId FROM user_info WHERE verification_code = ? AND is_verified = 0");
        $stmt->execute([$verificationCode]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $stmt = $pdo->prepare("UPDATE user_info SET is_verified = 1 WHERE userId = ?");
            if ($stmt->execute([$user['userId']])) {
                $_SESSION['message'] = "Your email has been verified! You can now log in.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
        } else {
            $error = "Invalid or expired verification code.";
        }
    } else {
        $error = "Please enter a verification code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Senior Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="fixed top-0 left-0 w-full bg-blue-600 text-white shadow-md z-50 py-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="../index.php" class="text-2xl font-bold">
                <span class="text-cyan-400">Senior</span> - Connect
            </a>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="../index.php" class="hover:text-cyan-300">Home</a></li>
                    <li><a href="#" class="hover:text-cyan-300">About Us</a></li>
                    <li><a href="#" class="hover:text-cyan-300">Services</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-96">
            <h2 class="text-2xl font-bold text-center mb-4">Email Verification</h2>
            <p class="text-gray-600 text-center mb-6">Enter your verification code below.</p>

            <?php if (!empty($error)): ?>
                <p class="text-red-500 text-center"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <p class="text-green-500 text-center"><?php echo $success; ?></p>
            <?php endif; ?>

            <form method="POST">
                <label class="block mb-2 text-gray-700">Verification Code</label>
                <input type="text" name="verification_code" class="w-full border rounded p-2 mb-4" placeholder="Enter your code" required>

                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                    Verify Email
                </button>
            </form>

            <p class="text-center text-gray-600 mt-4">
                Didn't receive a code? <a href="resend.php" class="text-blue-500 hover:underline">Resend Verification Email</a>
            </p>
        </div>
        <div class="md:w-1/2">
            <img src="../assets/Images/image2.png" alt="Healthcare illustration" class="w-full max-w-xl mx-auto">
        </div>
    </div>
</body>
</html>
