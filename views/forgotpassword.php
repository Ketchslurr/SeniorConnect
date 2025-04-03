<?php
session_start();
include '../config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if email exists in database
    $stmt = $pdo->prepare("SELECT userId FROM user_info WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Insert token into database
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expiry]);

        // Send reset email
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp-relay.brevo.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '8932ef001@smtp-brevo.com';
            $mail->Password   = '3Xpj6dJGQkI1MPsW';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('wolfjay08@gmail.com', 'Senior Connect');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "<p>Click the link below to reset your password:</p>
                <p><a href='http://senior-production-f9d8.up.railway.app/views/resetpassword.php?token=\$token'>Reset Password</a></p>
                <p>If you did not request a password reset, please ignore this email.</p>";
            $mail->AltBody = "Click the link to reset your password: http://senior-production-f9d8.up.railway.app/views/resetpassword.php?token=\$token";
            $mail->send();

            $_SESSION['message'] = "Password reset email sent! Please check your inbox.";
        } catch (Exception $e) {
            $_SESSION['error'] = "Email could not be sent. Mailer Error: {\$mail->ErrorInfo}";
        }
    } else {
        $_SESSION['error'] = "Email not found in our records.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-6 rounded-lg shadow-md max-w-sm w-full text-center">
        <h2 class="text-2xl font-bold mb-4">Forgot Password</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <p class="text-green-600"> <?= $_SESSION['message']; unset($_SESSION['message']); ?> </p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="text-red-600"> <?= $_SESSION['error']; unset($_SESSION['error']); ?> </p>
        <?php endif; ?>
        <form action="" method="POST" class="space-y-4">
            <input type="email" name="email" placeholder="Enter your email" required class="w-full p-2 border rounded">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 w-full">
                Send Reset Link
            </button>
        </form>
    </div>
</body>
</html>
