<?php
session_start();
include '../config.php';

if (!isset($_SESSION['googleEmail']) || !isset($_SESSION['userId'])) {
    header("Location: ../index.php");
    exit();
}

$googleName = $_SESSION['googleName'];
$googleEmail = $_SESSION['googleEmail'];
$userId = $_SESSION['userId'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $roleId = $_POST['roleId'];

    // Update role in user_info table
    $stmt = $pdo->prepare("UPDATE user_info SET roleId = ? WHERE userId = ?");
    $stmt->execute([$roleId, $userId]);

    $_SESSION['role'] = $roleId;

    // If Healthcare Professional, insert into healthcareprofessional table
    if ($roleId == 3) {
        // Check if the user is already in the healthcareprofessional table
        $stmt = $pdo->prepare("SELECT professionalId FROM healthcareprofessional WHERE userId = ?");
        $stmt->execute([$userId]);
        $professional = $stmt->fetch();

        if (!$professional) {
            // Insert new healthcare professional entry
            $stmt = $pdo->prepare("INSERT INTO healthcareprofessional (userId, fname, email) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $googleName, $googleEmail]);

            $_SESSION['professionalId'] = $pdo->lastInsertId();
        } else {
            $_SESSION['professionalId'] = $professional['professionalId'];
        }

        header("Location: ../views/HealthcareProfessional/healthcareDashboard.php");
    } else {
        header("Location: ../views/SeniorCitizen/seniorCitizenDashboard.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Role</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex justify-center items-center min-h-screen bg-gray-100">

    <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-700 mb-4">Welcome, <?= htmlspecialchars($googleName) ?>!</h2>
        <p class="text-lg text-gray-600 mb-6">Choose your role to continue.</p>

        <div class="flex flex-col md:flex-row justify-center gap-8">
            <form method="POST">
                <button type="submit" name="roleId" value="2" 
                    class="w-90 h-90 bg-white shadow-lg rounded-2xl p-6 flex flex-col justify-center items-center transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <img src="../assets/icons/seniorcitizen.png" alt="Senior Citizen" class="w-30 h-30">
                    <p class="text-xl font-semibold text-gray-800 mt-4">I am a Senior Citizen</p>
                </button>
            </form>

            <form method="POST">
                <button type="submit" name="roleId" value="3" 
                    class="w-90 h-90 bg-white shadow-lg rounded-2xl p-6 flex flex-col justify-center items-center transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <img src="../assets/icons/doctor.png" alt="Healthcare Professional" class="w-30 h-30">
                    <p class="text-xl font-semibold text-gray-800 mt-4">I am a Doctor</p>
                </button>
            </form>
        </div>
    </div>

</body>
</html>
