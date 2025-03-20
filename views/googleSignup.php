<?php
session_start();
include '../config.php';

// Ensure roleId is set, otherwise redirect to role selection
if (!isset($_SESSION['roleId'])) {
    header("Location: select_role.php");
    exit();
}

// Get roleId from session
$roleId = $_SESSION['roleId'];

// Ensure user is logged in via Google
if (!isset($_SESSION['googleEmail'])) {
    header("Location: login.php");
    exit();
}

// Pre-fill user data from Google
$googleEmail = $_SESSION['googleEmail'];
$googleName = $_SESSION['googleName']; // This could be full name, split it into first/last name

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];

    // Insert into user_info table
    $stmt = $pdo->prepare("INSERT INTO user_info (fname, lname, age, gender, email, roleId) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$firstName, $lastName, $age, $gender, $googleEmail, $roleId])) {
        $userId = $pdo->lastInsertId(); // Get the new user ID

        // Insert into appropriate role-based table
        if ($roleId == 3) {
            $stmt = $pdo->prepare("INSERT INTO healthcareprofessional (userId, fname, lname, age, gender, email) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $firstName, $lastName, $age, $gender, $googleEmail]);
            $redirectUrl = "HealthcareProfessional/healthcareDashboard.php";
        } elseif ($roleId == 2) {
            $stmt = $pdo->prepare("INSERT INTO seniorcitizen (userId, fname, lname, age, gender, email) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $firstName, $lastName, $age, $gender, $googleEmail]);
            $redirectUrl = "SeniorCitizen/seniorCitizenDashboard.php";
        }

        // Clear role selection after signup
        unset($_SESSION['roleId']);

        // Redirect to respective dashboard
        header("Location: $redirectUrl");
        exit();
    } else {
        $error = "Signup failed. Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Google Sign-Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex justify-center items-center min-h-screen">

    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
        <h2 class="text-3xl font-bold text-center text-gray-700">Complete Your Profile</h2>
        <p class="text-gray-500 text-center mt-2">Finish setting up your account</p>

        <?php if (isset($error)) echo "<p class='text-red-500 text-center mt-2'>$error</p>"; ?>

        <form method="POST" class="mt-6 space-y-4">
            <div class="flex space-x-4">
                <div class="w-1/2">
                    <label class="block text-gray-600 font-medium">First Name</label>
                    <input type="text" name="fName" class="w-full border border-gray-300 p-3 rounded-lg" placeholder="John" required>
                </div>
                <div class="w-1/2">
                    <label class="block text-gray-600 font-medium">Last Name</label>
                    <input type="text" name="lName" class="w-full border border-gray-300 p-3 rounded-lg" placeholder="Doe" required>
                </div>
            </div>

            <div>
                <label class="block text-gray-600 font-medium">Age</label>
                <input type="number" name="age" class="w-full border border-gray-300 p-3 rounded-lg" placeholder="Enter your age" required>
            </div>

            <div>
                <label class="block text-gray-600 font-medium">Gender</label>
                <select name="gender" class="w-full border border-gray-300 p-3 rounded-lg" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-gray-600 font-medium">Email</label>
                <input type="email" class="w-full border border-gray-300 p-3 rounded-lg bg-gray-200 cursor-not-allowed" 
                       value="<?= htmlspecialchars($googleEmail) ?>" disabled>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg text-lg font-medium hover:bg-blue-700 transition duration-200">
                Complete Sign-Up
            </button>
        </form>
    </div>

</body>
</html>
