<?php
include '../config.php';
session_start();
require '../vendor/autoload.php';

use GuzzleHttp\Client as GuzzleClient;

$client = new Google\Client();
$client->setAuthConfig(__DIR__ . '/../auth/client_secret.json');
$client->setRedirectUri('https://senior-production-f9d8.up.railway.app/auth/oauth-callback.php');
$client->addScope('email');
$client->addScope('profile');
$client->setAccessType('offline');
$client->setPrompt('consent');

if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        // Set access token
        $client->setAccessToken($token);

        $_SESSION['access_token'] = $token;

        
        // If refresh_token is returned (only on first consent)
        if (isset($token['refresh_token'])) {
            $_SESSION['refresh_token'] = $token['refresh_token'];
        }

        if (isset($token['error'])) {
            throw new Exception("Token error: " . json_encode($token));
        }

  

        // Fetch user info using Google Client (more reliable)
        $oauthService = new Google\Service\Oauth2($client);
        $userInfo = $oauthService->userinfo->get();

        $email = $userInfo->email;
        $fullName = $userInfo->name;

        // Check if user exists
        $stmt = $pdo->prepare("SELECT ui.userId, ui.fname, ui.roleId, hp.professionalId 
                               FROM user_info ui 
                               LEFT JOIN healthcareprofessional hp ON ui.userId = hp.userId 
                               WHERE ui.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['userId'] = $user['userId'];
            $_SESSION['fname'] = $user['fname'];
            $_SESSION['role'] = $user['roleId'];

            if ($user['roleId'] == 3) {
                $_SESSION['professionalId'] = $user['professionalId'];
            }

            if (empty($user['roleId'])) {
                $_SESSION['googleName'] = $fullName;
                $_SESSION['googleEmail'] = $email;
                header("Location: ../views/select_role.php");
                exit();
            }
        } else {
            $stmt = $pdo->prepare("INSERT INTO user_info (fName, email, roleId) VALUES (?, ?, NULL)");
            $stmt->execute([$fullName, $email]);

            $_SESSION['userId'] = $pdo->lastInsertId();
            $_SESSION['fName'] = $fullName;
            $_SESSION['role'] = null;
            $_SESSION['googleName'] = $fullName;
            $_SESSION['googleEmail'] = $email;

            header("Location: ../views/select_role.php");
            exit();
        }

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

    } catch (Exception $e) {
        echo "<pre>Authentication error:\n" . htmlspecialchars($e->getMessage()) . "</pre>";
        exit();
    }
}
