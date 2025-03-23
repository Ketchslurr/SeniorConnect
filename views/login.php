<?php
include '../config.php';
session_start();
require '../vendor/autoload.php';

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/../auth/client_secret_573751304329-u46e5l3l4o001omab337gl4e9jbsh8a8.apps.googleusercontent.com.json');
$client->setRedirectUri('https://senior-production-f9d8.up.railway.app/auth/oauth-callback.php'); // Update for Railway deployment
$client->addScope('email');
$client->addScope('profile');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT ui.userId, ui.Username, ui.fname, ui.Age, ui.email, ui.pwd, ui.roleId, 
                                hp.professionalId, hp.doctorEmail, sc.seniorId, sc.seniorEmail
                            FROM user_info ui 
                            LEFT JOIN healthcareprofessional hp ON ui.userId = hp.userId 
                            LEFT JOIN seniorcitizen sc ON ui.userId = sc.userId
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

        switch ($user['roleId']) {
            case 1:
                header("Location: ../views/Admin/adminDashboard.php");
                exit();
            case 2:
                $_SESSION['seniorId'] = $user['seniorId'];
                header("Location: ../views/SeniorCitizen/seniorCitizenDashboard.php");
                exit();
            case 3:
                $_SESSION['professionalId'] = $user['professionalId'];
                $_SESSION['doctorEmail'] = $user['doctorEmail'];
                header("Location: ../views/HealthcareProfessional/healthcareDashboard.php");
                exit();
            default:
                $error = "Invalid role assigned.";
                break;
        }
    } else {
        $error = "Invalid email or password";
    }
}

// Google Login URL
$googleAuthUrl = $client->createAuthUrl();
?>
