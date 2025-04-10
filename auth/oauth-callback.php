<?php
include '../config.php';
session_start();
require '../vendor/autoload.php';

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/../auth/client_secret_573751304329-u46e5l3l4o001omab337gl4e9jbsh8a8.apps.googleusercontent.com.json');
$client->setRedirectUri('https://senior-production-f9d8.up.railway.app/auth/oauth-callback.php');
$client->addScope('email');
$client->addScope('profile');
$client->setAccessType('offline');
$client->setPrompt('consent');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if ($client->isAccessTokenExpired()) {
        // Handle token expiration, possibly refresh the token
        echo "Token expired!";
        exit();
    }

    if (!isset($token['error'])) {
        $client->setAccessToken($token);
        $oauthService = new Google\Service\Oauth2($client);
        $userInfo = $oauthService->userinfo->get();

        $email = $userInfo->email;
        $fullName = $userInfo->name;

        // Check if user already exists
        $stmt = $pdo->prepare("SELECT ui.userId, ui.fname, ui.roleId, hp.professionalId 
                               FROM user_info ui 
                               LEFT JOIN healthcareprofessional hp ON ui.userId = hp.userId 
                               WHERE ui.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['userId'] = $user['userId'];
            // $_SESSION['username'] = $user['Username'];
            $_SESSION['fname'] = $user['fname'];
            $_SESSION['role'] = $user['roleId'];

            // Store professionalId if the user is a Healthcare Professional
            if ($user['roleId'] == 3) {
                $_SESSION['professionalId'] = $user['professionalId'];
            }
            

            // If user has no role, redirect to role selection
            if (empty($user['roleId'])) {
                $_SESSION['googleName'] = $fullName;
                $_SESSION['googleEmail'] = $email;
                header("Location: ../views/select_role.php");
                exit();
            }
        } else {
            // Insert new user without a role
            $stmt = $pdo->prepare("INSERT INTO user_info (fName, email, roleId) VALUES (?, ?, ?, NULL)");
            $stmt->execute([$fullName, $fullName, $email]);

            $_SESSION['userId'] = $pdo->lastInsertId();
            // $_SESSION['username'] = $fullName;
            $_SESSION['fName'] = $fullName;
            $_SESSION['role'] = null;
            $_SESSION['googleName'] = $fullName;
            $_SESSION['googleEmail'] = $email;

            // Redirect to role selection
            header("Location: ../views/select_role.php");
            exit();
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
                die("Invalid role assigned.");
        }
        exit();
    } else {
        echo "Authentication failed: " . $token['error'];
    }
}
?>
