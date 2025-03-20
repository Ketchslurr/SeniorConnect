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

//     echo "<pre>";
// print_r($_POST);
// print_r($_FILES);
// echo "</pre>";
// exit();

    // Handle profile picture upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $profile_picture = file_get_contents($_FILES['profile_picture']['tmp_name']);
    }

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Prepare SQL query
        if ($profile_picture !== null) {
            $sql = "UPDATE healthcareprofessional SET fname = :fname, lname = :lname, email = :email, specialization = :specialization, profile_picture = :profile_picture WHERE userId = :userId";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':profile_picture', $profile_picture, PDO::PARAM_LOB);
        } else {
            $sql = "UPDATE healthcareprofessional SET fname = :fname, lname = :lname, email = :email, specialization = :specialization WHERE userId = :userId";
            $stmt = $pdo->prepare($sql);
        }

        // Bind other parameters
        $stmt->bindParam(':fname', $fname);
        $stmt->bindParam(':lname', $lname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':specialization', $specialization);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Commit the transaction
            $pdo->commit();
            header("Location: ../HealthcareProfessional/services.php?success=1");
            exit();
        } else {
            throw new Exception("Database error.");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error updating profile: " . $e->getMessage();
    }
}
?>
