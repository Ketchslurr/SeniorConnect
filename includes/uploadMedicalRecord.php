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
    $uploadDir = "../../uploads/medical_records/";
    $file = $_FILES['medicalRecord'];

    // Validate file type (allow PDF, JPG, PNG)
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo "Error: Only PDF, JPG, and PNG files are allowed.";
        exit();
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo "Error: File size exceeds 5MB.";
        exit();
    }

    // Generate unique file name
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = "medical_" . $seniorId . "_" . time() . "." . $fileExtension;
    $filePath = $uploadDir . $newFileName;

    // Move file to uploads directory
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        // Save file info to database
        $sql = "INSERT INTO medical_records (seniorId, file_name, uploaded_at) VALUES (:seniorId, :fileName, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['seniorId' => $seniorId, 'fileName' => $newFileName]);

        echo "Medical record uploaded successfully!";
    } else {
        echo "Error: Failed to upload the file.";
    }
} else {
    echo "Error: No file uploaded.";
}
?>
