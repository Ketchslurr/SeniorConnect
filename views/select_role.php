<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['roleId'] = $_POST['roleId'];
    header("Location: signup.php");
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
        <h2 class="text-3xl font-bold text-gray-700 mb-8">Choose Your Role</h2>
        <div class="flex flex-col md:flex-row justify-center gap-8">

            <!-- Senior Citizen Card -->
            <form method="POST" class="w-80">
                <button type="submit" name="roleId" value="2" 
                    class="w-90 h-90 bg-white shadow-lg rounded-2xl p-6 flex flex-col justify-center items-center transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                    <img src="../assets/icons/seniorcitizen.png" alt="Senior Citizen" class="w-30 h-30">
                    <p class="text-xl font-semibold text-gray-800 mt-4">I am a Senior Citizen</p>
                </button>
               
            </form>

            <!-- Healthcare Professional Card -->
            <form method="POST" class="w-80">
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
