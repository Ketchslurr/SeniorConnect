<?php
session_start(); // Start session to get stored roleId
include '../config.php';
include '../includes/specializations.php';
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
    // $username = $_POST['username'];
    $password = $_POST['pwd'];
    $confirmPass = $_POST['confirm_pwd'];

    // Check if passwords match
    if ($password !== $confirmPass) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_info WHERE email = ?");
        $stmt->execute([$email]);
        $emailExists = $stmt->fetchColumn();

        if ($emailExists > 0) {
            $error = "Email is already registered. Please use a different email.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO user_info (fname, lname, age, gender, email, pwd, roleId) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$firstName, $lastName, $age, $gender, $email, $passwordHash, $roleId])) {
                $userId = $pdo->lastInsertId(); // Get the last inserted userId

                if ($roleId == 3) { // Doctor
                    $birthdate = $_POST['birthdate'];
                    $doctorLicenseNumber = $_POST['doctorLicenseNumber'];
                    $specialization = $_POST['specialization'];
                
                    // Handle license file upload
                    $targetDir = "../assets/uploads/licenses/";
                    $licenseFileName = basename($_FILES["licenseUpload"]["name"]);
                    $licenseTargetPath = $targetDir . $licenseFileName;
                    move_uploaded_file($_FILES["licenseUpload"]["tmp_name"], $licenseTargetPath);
                
                    $stmt = $pdo->prepare("INSERT INTO healthcareprofessional (userId, fname, lname, age, gender, doctorEmail, birthdate, doctorLicenseNumber, licenseUpload, specialization) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$userId, $firstName, $lastName, $age, $gender, $email, $birthdate, $doctorLicenseNumber, $licenseFileName, $specialization]);
                }
                if ($roleId == 2) {
                    $stmt = $pdo->prepare("INSERT INTO seniorcitizen (userId, fname, lname, age, gender, seniorEmail) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$userId, $firstName, $lastName, $age, $gender, $email]);
                }

                $_SESSION['success'] = true; // Set success flag
                header("Location: signup.php"); // Reload the page to trigger modal
                exit();
            } else {
                $error = "Signup failed. Try again.";
            }
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
            <form method="POST" onsubmit="return validateForm()" enctype="multipart/form-data" class="mt-4 space-y-4">
            <div class="flex space-x-4">
                    <input type="text" name="fName" class="w-1/2 border p-2 rounded" placeholder="First Name" required>
                    <input type="text" name="lName" class="w-1/2 border p-2 rounded" placeholder="Last Name" required>
                </div>
                <?php echo getSpecializationDropdown(); ?>
                <input type="date" name="birthdate" class="w-full border p-2 rounded" required>
                <input type="number" name="age" class="w-full border p-2 rounded" placeholder="Age" required>
                <select name="gender" class="w-full border p-2 rounded" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <input type="email" name="email" class="w-full border p-2 rounded" placeholder="Email" required>
                <input type="password" name="pwd" class="w-full border p-2 rounded" placeholder="Password" required>
                <input type="password" name="confirm_pwd" class="w-full border p-2 rounded" placeholder="Confirm Password" required>
                <input type="text" id="doctorLicenseNumber" name="doctorLicenseNumber" class="w-full border p-2 rounded" placeholder="License Number" required>
                <p id="error-message" class="text-red-500 text-sm hidden">Invalid License Number format. Example: D12-34-567890 or D1234567890</p>
                <input type="file" name="licenseUpload" class="w-full border p-2 rounded" accept=".pdf,.jpg,.png" required>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Sign Up</button>
            </form>
            <p class="text-center text-gray-600 mt-4">Already have an account? <a href="../views/login.php" class="text-blue-500 hover:underline">Login here!</a></p>
        </div>
        <div class="hidden md:block md:w-1/2">
            <img src="../assets/Images/image2.png" alt="Healthcare illustration" 
            class="w-full max-w-xl mx-auto">
        </div>
    </div>
    <script>
        function validateLicense() {
            let licenseInput = document.getElementById("doctorLicenseNumber").value;
            let errorMessage = document.getElementById("error-message");

            // Remove dashes for validation
            let formattedLicense = licenseInput.replace(/-/g, "");

            // PRC License Number format: A00-00-000000 or A0000000000
            let regex = /^[A-Za-z]\d{2}\d{2}\d{6}$/;

            if (!regex.test(formattedLicense)) {
                errorMessage.classList.remove("hidden"); // Show error message
                return false; // Prevent form submission
            } else {
                errorMessage.classList.add("hidden"); // Hide error message
                return true; // Allow form submission
            }
        }

        function validatePassword() {
            let password = document.getElementById("pwd").value;
            let confirmPassword = document.getElementById("confirm_pwd").value;
            let errorPassword = document.getElementById("password-error");

            // Password regex: Minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 number, 1 special character
            let passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

            if (!passwordRegex.test(password)) {
                errorPassword.innerText = "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
                errorPassword.classList.remove("hidden");
                return false;
            } else if (password !== confirmPassword) {
                errorPassword.innerText = "Passwords do not match.";
                errorPassword.classList.remove("hidden");
                return false;
            } else {
                errorPassword.classList.add("hidden");
                return true;
            }
        }

        // Main form validation function
        function validateForm() {
            let isLicenseValid = validateLicense();
            let isPasswordValid = validatePassword();
            return isLicenseValid && isPasswordValid; // Only submit if both are valid
        }
    </script>

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