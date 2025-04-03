<?php
include '../config.php';
session_start();
require '../vendor/autoload.php';
require_once 'authFunctions.php';

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/../auth/client_secret_573751304329-u46e5l3l4o001omab337gl4e9jbsh8a8.apps.googleusercontent.com.json');
$client->setRedirectUri('https://senior-production-f9d8.up.railway.app/auth/oauth-callback.php');
$client->addScope('email');
$client->addScope('profile');

// Handle normal login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // First, check if the user is an admin
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin["password"])) {
        // Admin login successful
        $_SESSION["adminId"] = $admin["adminId"];
        $_SESSION["role"] = 1; // Set roleId to 1 for admin
        header("Location: ../views/Admin/adminDashboard.php");
        exit();
    }

    // If not admin, check user_info table
    $stmt = $pdo->prepare("SELECT ui.userId, ui.fname, ui.Age, ui.email, ui.pwd, ui.roleId, 
                                hp.professionalId, hp.doctorEmail, sc.seniorId, sc.seniorEmail
                            FROM user_info ui 
                            LEFT JOIN healthcareprofessional hp ON ui.userId = hp.userId 
                            LEFT JOIN seniorcitizen sc ON ui.userId = sc.userId
                            WHERE ui.email = ? AND is_verified = 1");

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['pwd'])) {
        $_SESSION['userId'] = $user['userId'];
        // $_SESSION['username'] = $user['Username'];
        $_SESSION['fname'] = $user['fname'];
        $_SESSION['age'] = $user['Age'];
        $_SESSION['valid'] = $user['email'];
        $_SESSION['role'] = $user['roleId'];

        // Redirect based on role
        switch ($user['roleId']) {
            case 2:
                $_SESSION['seniorId'] = $user['seniorId'];
                header("Location: ../views/SeniorCitizen/seniorCitizenDashboard.php");
                break;
            case 3:
                $_SESSION['professionalId'] = $user['professionalId'];
                $_SESSION['doctorEmail'] = $user['doctorEmail'];
                header("Location: ../views/HealthcareProfessional/healthcareDashboard.php");
                break;
            default:
                $error = "Invalid role assigned.";
                break;
        }
        exit();
    } else {
        $error = "Invalid email or password";
    }
}

// Google Login URL
$googleAuthUrl = $client->createAuthUrl();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Senior Connect</title>
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
            <h2 class="text-2xl font-bold text-center mb-4">Welcome Back</h2>
            <p class="text-gray-600 text-center mb-6">Please enter your details to log in.</p>
            <?php if (isset($error)) echo "<p class='text-red-500 text-center'>$error</p>"; ?>
            <form method="POST">
                <label class="block mb-2 text-gray-700">Email</label>
                <input type="email" name="email" class="w-full border rounded p-2 mb-4" placeholder="Enter your email" required>
                
                <label class="block mb-2 text-gray-700">Password</label>
                <input type="password" name="password" class="w-full border rounded p-2 mb-4" placeholder="*********" required>
                
                <div class="flex justify-between items-center text-sm mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" class="mr-2"> Remember me
                    </label>
                    <a href="#" class="text-blue-500 hover:underline">Forgot password?</a>
                </div>
                
                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Login</button>
            </form>
            
            <!-- Google Sign-In Button -->
            <div class="mt-4 flex items-center justify-center bg-gray-100 p-2 rounded">
                <img src="../assets/Images/google.jpg" alt="Google Logo" class="h-6 w-6 mr-2">
                <a href="<?= htmlspecialchars($googleAuthUrl) ?>" class="text-gray-700">Sign in with Google</a>
            </div>
            
            <p class="text-center text-gray-600 mt-4">Don't have an account? <a href="../views/signup.php" class="text-blue-500 hover:underline">Sign up for free!</a></p>
        </div>
        <div class="md:w-1/2">
            <img src="../assets/Images/image2.png" alt="Healthcare illustration" class="w-full max-w-xl mx-auto">
        </div>
    </div>
</body>
</html>
