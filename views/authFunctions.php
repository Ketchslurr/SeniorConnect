<?php
include __DIR__ . '/../config.php';


function authenticateUser($pdo, $email, $password) {
    // Check if the user is an admin
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin["password"])) {
        return ['role' => 'admin', 'id' => $admin["adminId"]];
    }

    // Check if user exists in user_info table
    $stmt = $pdo->prepare("SELECT ui.userId, ui.fname, ui.Age, ui.email, ui.pwd, ui.roleId, 
                                hp.professionalId, hp.doctorEmail, sc.seniorId, sc.seniorEmail
                            FROM user_info ui 
                            LEFT JOIN healthcareprofessional hp ON ui.userId = hp.userId 
                            LEFT JOIN seniorcitizen sc ON ui.userId = sc.userId
                            WHERE ui.email = ? AND is_verified = 1");

    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['pwd'])) {
        return [
            'role' => $user['roleId'],
            'id' => $user['userId'],
            'fname' => $user['fname'],
            'age' => $user['Age']
        ];
    }

    return false;
}
?>
