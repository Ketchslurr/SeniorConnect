<?php
session_start();
include '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists and is not verified
    $stmt = $pdo->prepare("SELECT userId, fname FROM user_info WHERE email = ? AND is_verified = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $newCode = bin2hex(random_bytes(16)); // Generate a new verification code

        // Update verification code in the database
        $stmt = $pdo->prepare("UPDATE user_info SET verification_code = ? WHERE userId = ?");
        if ($stmt->execute([$newCode, $user['userId']])) {
            
            // ✅ Send verification email
            $subject = "Resend: Verify Your Email - Senior Connect";
            $message = "
                <h2>Email Verification</h2>
                <p>Hi <b>{$user['fname']}</b>,</p>
                <p>Click the link below to verify your email:</p>
                <a href='https://yourwebsite.com/auth/verify.php?code=$newCode' 
                   style='display: inline-block; padding: 10px 15px; background-color: blue; color: white; text-decoration: none; border-radius: 5px;'>
                   Verify Now
                </a>
                <p>If you didn't sign up, please ignore this email.</p>
            ";

            if (sendEmail($email, $subject, $message)) {
                $_SESSION['message'] = "A new verification email has been sent to $email.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Failed to send the email. Try again later.";
            }
        } else {
            $error = "Something went wrong. Please try again.";
        }
    } else {
        $error = "This email is either already verified or does not exist.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification - Senior Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-96">
            <h2 class="text-2xl font-bold text-center mb-4">Resend Verification Email</h2>
            <p class="text-gray-600 text-center mb-6">Enter your email to receive a new verification link.</p>
            
            <?php if (isset($error)) echo "<p class='text-red-500 text-center'>$error</p>"; ?>
            
            <form method="POST">
                <label class="block mb-2 text-gray-700">Email</label>
                <input type="email" name="email" class="w-full border rounded p-2 mb-4" placeholder="Enter your email" required>
                
                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Resend Email</button>
            </form>
            
            <p class="text-center text-gray-600 mt-4">
                Already verified? <a href="login.php" class="text-blue-500 hover:underline">Log in</a>
            </p>
        </div>
    </div>
</body>
</html>
