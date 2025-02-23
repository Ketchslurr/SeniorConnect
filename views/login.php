<?php
include '../config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // $stmt = $pdo->prepare("SELECT userId, Username,fname, Age, email, pwd, roleId FROM user_info WHERE email = ?");
    $stmt = $pdo->prepare("SELECT ui.userId, ui.Username, ui.fname, ui.Age, ui.email, ui.pwd, ui.roleId, hp.professionalId 
                       FROM user_info ui 
                       LEFT JOIN healthcareprofessional hp ON ui.userId = hp.userId 
                       WHERE ui.email = ?");

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['pwd'])) {
        $_SESSION['userId'] = $user['userId'];
        $_SESSION['username'] = $user['Username'];
        $_SESSION['fname'] = $user['fname'];
        $_SESSION['age'] = $user['Age'];
        $_SESSION['valid'] = $user['email'];
        $_SESSION['role'] = $user['roleId'];
        
        if ($user['roleId'] == 3) {
            $_SESSION['professionalId'] = $user['professionalId'];
        }
        // Redirect based on role
        switch ($user['roleId']) {
            case 1:
                header("Location: ../views/Admin/adminDashboard.php");
                break;
            case 2:
                header("Location: ../views/SeniorCitizen/seniorCitizenDashboard.php");
                break;
            case 3:
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
            
            <div class="mt-4 flex items-center justify-center bg-gray-100 p-2 rounded">
                <img src="../assets/Images/google.jpg" alt="Google Logo" class="h-6 w-6 mr-2">
                <a href="#" class="text-gray-700">Sign in with Google Account</a>
            </div>
            
            <p class="text-center text-gray-600 mt-4">Don't have an account? <a href="../views/signup.php" class="text-blue-500 hover:underline">Sign up for free!</a></p>
        </div>
        <div class="md:w-1/2">
            <img src="../assets/Images/image2.png" alt="Healthcare illustration" 
            class="w-full max-w-xl mx-auto">
        </div>
    </div>
</body>
</html>
