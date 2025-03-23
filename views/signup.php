<?php
session_start(); // Start session to get stored roleId
include '../config.php';

// Ensure roleId is set, otherwise redirect back to role selection
if (!isset($_SESSION['roleId'])) {
    header("Location: select_role.php");
    exit();
}

$roleId = $_SESSION['roleId']; // Retrieve stored role

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['pwd'];
    $confirmPass = $_POST['confirm_pwd'];

    if ($password !== $confirmPass) {
        $error = "Passwords do not match!";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO user_info (fname, lname, age, gender, email, username, pwd, roleId) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$firstName, $lastName, $age, $gender, $email, $username, $passwordHash, $roleId])) {
            $userId = $pdo->lastInsertId(); // Get the last inserted userId
        
            if ($roleId == 3) {
                $stmt = $pdo->prepare("INSERT INTO healthcareprofessional (userId, fname, lname, age, gender, doctorEmail) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $firstName, $lastName, $age, $gender, $email]);
            }
            if ($roleId == 2) {
                $stmt = $pdo->prepare("INSERT INTO seniorcitizen (userId, fname, lname, age, gender, seniorEmail) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $firstName, $lastName, $age, $gender, $email]);
            }
        
            // unset($_SESSION['roleId']); // Clear role selection after signup
        
            $_SESSION['success'] = true; // Set success flag
            header("Location: signup.php"); // Reload the page to trigger modal
            exit();
                
        } else {
            $error = "Signup failed. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <header class="fixed top-0 left-0 w-full bg-blue-600 text-white shadow-md z-50 py-4">
        <div class="container mx-auto flex justify-between items-center px-6">
            <h2 class="text-2xl font-bold"><a href="../index.php">Senior <span class="text-cyan-300">Connect</span></a></h2>
            <nav>
                <ul class="flex space-x-6">
                    <li><a href="../index.php" class="hover:underline">Home</a></li>
                    <li><a href="#" class="hover:underline">About Us</a></li>
                    <li><a href="services.php" class="hover:underline">Services</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="flex justify-center items-center min-h-screen px-4">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold text-center">Sign Up</h2>
            <?php if (isset($error)) echo "<p class='text-red-500 text-center mt-2'>$error</p>"; ?>
            <form method="POST" class="mt-4 space-y-4">
                <div class="flex space-x-4">
                    <input type="text" name="fName" class="w-1/2 border p-2 rounded" placeholder="First Name" required>
                    <input type="text" name="lName" class="w-1/2 border p-2 rounded" placeholder="Last Name" required>
                </div>
                <input type="number" name="age" class="w-full border p-2 rounded" placeholder="Age" required>
                <select name="gender" class="w-full border p-2 rounded" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <input type="email" name="email" class="w-full border p-2 rounded" placeholder="Email" required>
                <input type="text" name="username" class="w-full border p-2 rounded" placeholder="Username" required>
                <input type="password" name="pwd" class="w-full border p-2 rounded" placeholder="Password" required>
                <input type="password" name="confirm_pwd" class="w-full border p-2 rounded" placeholder="Confirm Password" required>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Sign Up</button>
            </form>
            <p class="text-center text-gray-600 mt-4">Already have an account? <a href="../views/login.php" class="text-blue-500 hover:underline">Login here!</a></p>
        </div>
        <div class="hidden md:block md:w-1/2">
            <img src="../assets/Images/image2.png" alt="Healthcare illustration" 
            class="w-full max-w-xl mx-auto">
        </div>
    </div>
    <?php if (isset($_SESSION['success']) && $_SESSION['success'] === true): ?>
        <div id="successModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-96 text-center">
                <h3 class="text-lg font-bold text-green-600">Account Created Successfully!</h3>
                <p class="text-gray-700 mt-2">Your account has been successfully created. You can now log in.</p>
                <button onclick="closeModal()" class="mt-4 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700">OK</button>
            </div>
        </div>
        <script>
            function closeModal() {
                document.getElementById('successModal').style.display = 'none';
                window.location.href = "login.php"; // Redirect to login page
            }
        </script>
        <?php unset($_SESSION['success']); // Remove the success flag after showing the modal ?>
    <?php endif; ?>

</body>
</html>