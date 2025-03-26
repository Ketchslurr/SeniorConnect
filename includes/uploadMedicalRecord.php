<?php
include '../config.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['seniorId'])) {
    header("Location: login.php");
    exit();
}

$seniorId = $_SESSION['seniorId'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['medicalRecord'])) {
    $uploadDir = "../assets/uploads/medical_records/";
    $file = $_FILES['medicalRecord'];

    // Validate file type (allow PDF, JPG, PNG)
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowedTypes)) {
        $_SESSION['upload_status'] = "Error: Only PDF, JPG, and PNG files are allowed.";
        $_SESSION['upload_status_type'] = "error";
        exit();
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        $_SESSION['upload_status'] = "Error: File size exceeds 5MB.";
        $_SESSION['upload_status_type'] = "error";
        exit();
    }

    // Generate unique file name
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = "medical_" . $seniorId . "_" . time() . "." . $fileExtension;
    $filePath = $uploadDir . $newFileName;

    // $uploadDir = "../../uploads/medical_records/";
    // if (!is_dir($uploadDir)) {
    //     mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    // }

    // Move file to uploads directory
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Save file info to database
        $sql = "INSERT INTO medical_records (seniorId, file_name, file_path, uploaded_at) 
                VALUES (:seniorId, :fileName, :filePath, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
                            'seniorId' => $seniorId,
                            'fileName' => $newFileName,
                            'filePath' => $filePath 
                        ]);

        $_SESSION['upload_status'] = "Medical record uploaded successfully!";
        $_SESSION['upload_status_type'] = "success";
    } else {
        $_SESSION['upload_status'] = "Error: Failed to upload the file.";
        $_SESSION['upload_status_type'] = "error";
    }
} else {
    $_SESSION['upload_status'] = "Error: No file uploaded.";
    $_SESSION['upload_status_type'] = "error";
}
header("Location: ../views/SeniorCitizen/seniorProfile.php");
exit();
?>
