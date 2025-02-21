<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_POST['userId'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $specialization = $_POST['specialization'];

    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $profile_picture = file_get_contents($_FILES['profile_picture']['tmp_name']);
    }

    // Prepare SQL query
    $sql = "UPDATE healthcareprofessional SET fname = :fname, lname = :lname, email = :email, specialization = :specialization";

    if ($profile_picture !== null) {
        $sql .= ", profile_picture = :profile_picture";
    }

    $sql .= " WHERE userId = :userId";

    $stmt = $pdo->prepare($sql);
    $params = [
        'fname' => $fname,
        'lname' => $lname,
        'email' => $email,
        'specialization' => $specialization,
        'userId' => $userId
    ];

    if ($profile_picture !== null) {
        $params['profile_picture'] = $profile_picture;
    }

    if ($stmt->execute($params)) {
        header("Location: ../HealthcareProfessional/services.php?success=1");
    } else {
        echo "Error updating profile.";
    }
}
?>
