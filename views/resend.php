<?php
session_start();
include '../config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // ✅ Step 1: Check if email exists and is not verified
    $stmt = $pdo->prepare("SELECT userId, fname FROM user_info WHERE email = ? AND is_verified = 0");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = "Email not found or already verified.";
    } else {
        // ✅ Step 2: Generate a new verification code
        $verificationCode = bin2hex(random_bytes(16));

        // ✅ Step 3: Update the verification code in the database
        $stmt = $pdo->prepare("UPDATE user_info SET verification_code = ? WHERE userId = ?");
        if ($stmt->execute([$verificationCode, $user['userId']])) {
            
            // ✅ Step 4: Send a new verification email
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp-relay.brevo.com'; // Change to your SMTP server
                $mail->SMTPAuth   = true;
                $mail->Username   = '8932ef001@smtp-brevo.com'; // Your email
                $mail->Password   = '3Xpj6dJGQkI1MPsW'; // Your email password or App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('wolfjay08@gmail.com', 'Senior Connect');
                $mail->addAddress($email, $user['fname']);

                // Email Content
                $mail->isHTML(true);
                $mail->Subject = 'Resend: Verify Your Email Address';
                $mail->Body    = "
                    <p>Hello <strong>{$user['fname']}</strong>,</p>
                    <p>Please verify your email by clicking the link below:</p>
                    <p><a href='http://senior-production-f9d8.up.railway.app/views/verifyEmail.php?code=$verificationCode'>Verify Email</a></p>
                    <p>Or manually enter this verification code:</p>
                    <h2 style='color: blue;'>$verificationCode</h2>
                    <p>Thank you!</p>
                ";

                $mail->AltBody = "Hello {$user['fname']}, Please verify your email by clicking the link: http://senior-production-f9d8.up.railway.app/views/verifyEmail.php?code=$verificationCode or manually enter this code: $verificationCode";

                $mail->send();

                $success = "Verification email resent successfully! Please check your inbox.";
            } catch (Exception $e) {
                $error = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "Failed to generate a new verification code. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification Email</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex justify-center items-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-lg w-96">
            <h2 class="text-2xl font-bold text-center mb-4">Resend Verification Email</h2>
            <p class="text-gray-600 text-center mb-6">Enter your email to receive a new verification link.</p>

            <?php if (!empty($error)): ?>
                <p class="text-red-500 text-center"><?php echo $error; ?></p>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <p class="text-green-500 text-center"><?php echo $success; ?></p>
            <?php endif; ?>

            <form method="POST">
                <label class="block mb-2 text-gray-700">Email Address</label>
                <input type="email" name="email" class="w-full border rounded p-2 mb-4" placeholder="Enter your email" required>

                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                    Resend Email
                </button>
            </form>

            <p class="text-center text-gray-600 mt-4">
                <a href="login.php" class="text-blue-500 hover:underline">Back to Login</a>
            </p>
        </div>
    </div>
</body>
</html>
