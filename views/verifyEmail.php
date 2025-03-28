<?php
session_start();
include '../config.php';

if (isset($_GET['code'])) {
    $verificationCode = $_GET['code'];

    // ✅ Check if the verification code exists
    $stmt = $pdo->prepare("SELECT userId FROM user_info WHERE verification_code = ? AND is_verified = 0");
    $stmt->execute([$verificationCode]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // ✅ Mark the user as verified
        $stmt = $pdo->prepare("UPDATE user_info SET is_verified = 1, verification_code = NULL WHERE userId = ?");
        if ($stmt->execute([$user['userId']])) {
            $_SESSION['message'] = "Your email has been verified! You can now log in.";
            header("Location: login.php"); // Redirect to login page
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
        }
    } else {
        $error = "Invalid or expired verification code.";
    }
} else {
    $error = "No verification code provided.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <!-- <link rel="stylesheet" href="../assets/styles.css"> Adjust this path to your CSS file -->
</head>
<body>
    <div class="container">
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
