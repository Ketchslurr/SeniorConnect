<?php
session_start();
include '../config.php';
include '../includes/specializations.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Adjust path if necessary

if (!isset($_SESSION['roleId'])) {
    header("Location: select_role.php");
    exit();
}

$roleId = $_SESSION['roleId'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST['fName'];
    $lastName = $_POST['lName'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $password = $_POST['pwd'];
    $confirmPass = $_POST['confirm_pwd'];

    if ($password !== $confirmPass) {
        $error = "Passwords do not match!";
    } else {
        //  Step 1: Validate Email Using Abstract API
        $apiKey = "eaa405e188f24410b1b9c8b87a1a8b4e"; // Replace with your API key
        $apiUrl = "https://emailvalidation.abstractapi.com/v1/?api_key=$apiKey&email=$email";
        
        $response = file_get_contents($apiUrl);
        $result = json_decode($response, true);

        //Step 2: Check if email is valid
        if ($result['deliverability'] !== 'DELIVERABLE' || $result['is_disposable_email'] === true) {
            $error = "Invalid or temporary email. Please use a real email.";
        } else {
            // Step 3: Check if email is already registered
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_info WHERE email = ?");
            $stmt->execute([$email]);
            $emailExists = $stmt->fetchColumn();

            if ($emailExists > 0) {
                $error = "Email is already registered. Please use a different email.";
            } else {
                // Step 4: Generate Verification Code
                $verificationCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO user_info (fname, lname, age, gender, email, pwd, roleId, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
                
                if ($stmt->execute([$firstName, $lastName, $age, $gender, $email, $passwordHash, $roleId, $verificationCode])) {
                    $userId = $pdo->lastInsertId(); // Get last inserted userId

                    if ($roleId == 3) { // If Doctor
                        $birthdate = $_POST['birthdate'];
                        $doctorLicenseNumber = $_POST['doctorLicenseNumber'];
                        $specialization = $_POST['specialization'];

                        // Handle license upload
                        $targetDir = "../assets/uploads/licenses/";
                        $licenseFileName = basename($_FILES["licenseUpload"]["name"]);
                        $licenseTargetPath = $targetDir . $licenseFileName;
                        move_uploaded_file($_FILES["licenseUpload"]["tmp_name"], $licenseTargetPath);

                        $stmt = $pdo->prepare("INSERT INTO healthcareprofessional (userId, fname, lname, age, gender, doctorEmail, birthdate, doctorLicenseNumber, licenseUpload, specialization) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$userId, $firstName, $lastName, $age, $gender, $email, $birthdate, $doctorLicenseNumber, $licenseFileName, $specialization]);
                    }
                    if ($roleId == 2) { // If Senior Citizen
                        $stmt = $pdo->prepare("INSERT INTO seniorcitizen (userId, fname, lname, age, gender, seniorEmail) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$userId, $firstName, $lastName, $age, $gender, $email]);
                    }

                    //  Step 5: Send Verification Email Using PHPMailer
                    $mail = new PHPMailer(true);

                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host       = 'smtp-relay.brevo.com'; 
                        $mail->SMTPAuth   = true;
                        $mail->Username   = '8932ef001@smtp-brevo.com';
                        $mail->Password   = '3Xpj6dJGQkI1MPsW'; 
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;

                        // Recipients
                        $mail->setFrom('wolfjay08@gmail.com', 'Senior Connect');
                        $mail->addAddress($email, $firstName);

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Verify Your Email Address';
                        $mail->Body    = "
                            <p>Hello <strong>$firstName</strong>,</p>
                            <p>Please verify your email by clicking the link below:</p>
                            <p><a href='http://seniorconnect-production.up.railway.app/views/verifyEmail.php?code=$verificationCode'>Verify Email</a></p>
                            <p>Or manually enter this verification code:</p>
                            <h2 style='color: blue;'>$verificationCode</h2>
                            <p>Thank you!</p>
                        ";
                        $mail->AltBody = "Hello $firstName, Please verify your email by clicking the link: http://seniorconnect-production.up.railway.app/views/verifyEmail.php?code=$verificationCode or manually enter this code: $verificationCode";
                        $mail->send();

                        $_SESSION['message'] = "Verification email sent! Please check your inbox.";
                        header("Location: verifyEmail.php");
                        exit();
                    } catch (Exception $e) {
                        $error = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                } else {
                    $error = "Signup failed. Try again.";
                }
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
            <!-- <nav>
                <ul class="flex space-x-6">
                    <li><a href="../index.php" class="hover:underline">Home</a></li>
                    <li><a href="#" class="hover:underline">About Us</a></li>
                    <li><a href="services.php" class="hover:underline">Services</a></li>
                </ul>
            </nav> -->
        </div>
    </header>

    <div class="flex justify-center items-center min-h-screen px-4">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <h2 class="text-2xl font-bold text-center">Sign Up</h2>
            <?php if (isset($error)) echo "<p class='text-red-500 text-center mt-2'>$error</p>"; ?>
            <form method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                <div class="flex space-x-4">
                    <input type="text" name="fName" id="fName" class="w-1/2 border p-2 rounded" placeholder="First Name" required>
                    <input type="text" name="lName" id="lName" class="w-1/2 border p-2 rounded" placeholder="Last Name" required>
                </div>
                <p id="name-error" class="text-red-500 text-sm hidden">Names can only contain letters.</p>
                <label for="date">Birthdate</label>
                <input type="date" name="birthdate" id="birthdate" class="w-full border p-2 rounded" required>
                <input type="number" name="age" id="age" class="w-full border p-2 rounded bg-gray-200" readonly>
                <p id="age-error" class="text-red-500 text-sm hidden">Only seniors (60 years and older) can register.</p>

                <select name="gender" class="w-full border p-2 rounded" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <div id="doctorFields" class="hidden">
                    <?php echo getSpecializationDropdown(); ?>
                    <input type="text" id="doctorLicenseNumber" name="doctorLicenseNumber" class="w-full border p-2 rounded" placeholder="License Number">
                    <p id="error-message" class="text-red-500 text-sm hidden">Invalid License Number format. Example: D12-34-567890 or D1234567890</p>
                </div>
                <label for="licenseUpload" class="block">Upload Valid ID</label>
                <input type="file" name="licenseUpload" class="w-full border p-2 rounded" accept=".pdf,.jpg,.png" required>
                <input type="email" name="email" class="w-full border p-2 rounded" placeholder="Email" required>
                <input type="password" id="pwd" name="pwd" class="w-full border p-2 rounded" placeholder="Password" required>
                <input type="password" id="confirm_pwd" name="confirm_pwd" class="w-full border p-2 rounded" placeholder="Confirm Password" required>
                <p id="password-error" class="text-red-500 text-sm hidden">Password must be at least 8 characters long, including an uppercase letter, lowercase letter, number, and special character.</p>
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
        let roleId = <?php echo isset($_SESSION['roleId']) ? json_encode($_SESSION['roleId']) : 'null'; ?>;

        document.addEventListener("DOMContentLoaded", function () {
            let birthdateInput = document.getElementById("birthdate");
            let ageInput = document.getElementById("age");
            let ageError = document.getElementById("age-error");
            let form = document.querySelector("form");
            let nameError = document.getElementById("name-error");

            document.getElementById("fName").addEventListener("input", validateName);
            document.getElementById("lName").addEventListener("input", validateName);

            function validateName() {
                let fName = document.getElementById("fName").value;
                let lName = document.getElementById("lName").value;
                let nameRegex = /^[A-Za-z]+$/;
                if (!nameRegex.test(fName) || !nameRegex.test(lName)) {
                    nameError.classList.remove("hidden");
                } else {
                    nameError.classList.add("hidden");
                }
            }

            birthdateInput.addEventListener("change", function () {
                let birthdate = new Date(this.value);
                let today = new Date();
                let age = today.getFullYear() - birthdate.getFullYear();
                let monthDiff = today.getMonth() - birthdate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }
                ageInput.value = age;
                if (roleId == "2" && age < 60) {
                    ageError.classList.remove("hidden");
                } else {
                    ageError.classList.add("hidden");
                }
            });

            form.addEventListener("submit", function (event) {
                if (roleId == "2" && parseInt(ageInput.value) < 60) {
                    event.preventDefault();
                    ageError.classList.remove("hidden");
                }
            });

            if (roleId == "3") {
                document.getElementById("doctorFields").classList.remove("hidden");
            }
        });
    </script>

    <script>
        let roleId = <?php echo isset($_SESSION['roleId']) ? json_encode($_SESSION['roleId']) : 'null'; ?>;

        


        document.addEventListener("DOMContentLoaded", function () {
            let birthdateInput = document.getElementById("birthdate");
            let ageInput = document.getElementById("age");
            let ageError = document.getElementById("age-error");
            let form = document.querySelector("form");

            birthdateInput.addEventListener("change", function () {
                let birthdate = new Date(this.value);
                let today = new Date();
                let age = today.getFullYear() - birthdate.getFullYear();
                let monthDiff = today.getMonth() - birthdate.getMonth();

                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }

                ageInput.value = age;

                if (age < 60) {
                    ageError.classList.remove("hidden");
                } else {
                    ageError.classList.add("hidden");
                }
            });

            form.addEventListener("submit", function (event) {
                let age = parseInt(ageInput.value);
                if (age < 60) {
                    event.preventDefault(); // Stop form submission
                    ageError.classList.remove("hidden");
                }
            });
        });


        function toggleFields() {
            let doctorFields = document.getElementById("doctorFields");
            let licenseUpload = document.querySelector("input[name='licenseUpload']");
            let licenseLabel = document.querySelector("label[for='licenseUpload']");
        
                

                if (roleId == "3") { // Doctor
                    doctorFields.classList.remove("hidden");
                }
            }

            // Run function on page load to set correct visibility
            window.onload = toggleFields;


            document.addEventListener("DOMContentLoaded", function () {
                let licenseInput = document.getElementById("doctorLicenseNumber");
                let passwordInput = document.getElementById("pwd");
                let confirmPasswordInput = document.getElementById("confirm_pwd");
                let form = document.querySelector("form");

                licenseInput.addEventListener("blur", validateLicense);
                passwordInput.addEventListener("blur", validatePassword);
                confirmPasswordInput.addEventListener("blur", validatePassword);

                function validateLicense() {
                    let licenseValue = licenseInput.value.trim();
                    let errorMessage = document.getElementById("error-message");
                    let regex = /^[A-Za-z]\d{2}-\d{2}-\d{6}$|^[A-Za-z]\d{10}$/;

                    if (!regex.test(licenseValue)) {
                        errorMessage.classList.remove("hidden");
                        return false;
                    } else {
                        errorMessage.classList.add("hidden");
                        return true;
                    }
                }

                function validatePassword() {
                    let password = passwordInput.value.trim();
                    let confirmPassword = confirmPasswordInput.value.trim();
                    let errorPassword = document.getElementById("password-error");
                    let passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

                    if (!passwordRegex.test(password)) {
                        errorPassword.innerText = "Password must have 8+ characters, one uppercase, one lowercase, one number, and one special character.";
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

                form.addEventListener("submit", function (event) {
                    let isLicenseValid = validateLicense();
                    let isPasswordValid = validatePassword();

                    if (!isPasswordValid) {
                        event.preventDefault(); // Stop submission if any validation fails
                    } else if (!isLicenseValid){
                        event.preventDefault();
                    }
                });
            });

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