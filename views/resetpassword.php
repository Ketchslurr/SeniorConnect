<?php
session_start();
include '../config.php';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Invalid or expired reset link.");
}

$token = $_GET['token'];

// Verify token validity
$stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expiry > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Invalid or expired reset link.");
}

$email = $user['email'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Update password in user_info
        $stmt = $pdo->prepare("UPDATE user_info SET pwd = ? WHERE email = ?");
        if ($stmt->execute([$hashedPassword, $email])) {
            // Delete the token after reset
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);
            
            $_SESSION['message'] = "Password reset successful! You can now login.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    
    <form action="" method="POST">
        <label>New Password:</label>
        <input type="password" name="password" required>
        
        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required>
        
        <button type="submit">Reset Password</button>
    </form>
</body>
</html>
