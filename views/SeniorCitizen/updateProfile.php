<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$seniorId = $_SESSION['seniorId'];

// Fetch current senior details
$sql = "SELECT * FROM seniorcitizen WHERE seniorId = :seniorId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['seniorId' => $seniorId]);
$senior = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $age = $_POST['age'];
    $blood_pressure = $_POST['blood_pressure'];
    $heart_rate = $_POST['heart_rate'];
    $oxygen_level = $_POST['oxygen_level'];

    // Handle profile picture upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = "../../uploads/profile_pictures/";
        $fileName = basename($_FILES['profile_picture']['name']);
        $targetFilePath = $targetDir . $fileName;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath);
    } else {
        $fileName = $senior['profile_picture']; // Use existing picture if no new file is uploaded
    }

    // **Check if profile_picture exists in the database**
    $checkColumnSQL = "SHOW COLUMNS FROM seniorCitizen LIKE 'profile_picture'";
    $columnStmt = $pdo->prepare($checkColumnSQL);
    $columnStmt->execute();
    $profilePictureExists = $columnStmt->rowCount() > 0;

    // Prepare SQL query
    if ($profilePictureExists) {
        $sql = "UPDATE seniorcitizen SET 
                    fName = :fName, 
                    lName = :lName, 
                    age = :age, 
                    blood_pressure = :blood_pressure, 
                    heart_rate = :heart_rate, 
                    oxygen_level = :oxygen_level, 
                    profile_picture = :profile_picture 
                WHERE seniorId = :seniorId";

        $params = [
            'fName' => $fname,
            'lName' => $lname,
            'age' => $age,
            'blood_pressure' => $blood_pressure,
            'heart_rate' => $heart_rate,
            'oxygen_level' => $oxygen_level,
            'profile_picture' => $fileName,
            'seniorId' => $seniorId
        ];
    } else {
        $sql = "UPDATE seniorcitizen SET 
                    fName = :fName, 
                    lName = :lName, 
                    age = :age, 
                    blood_pressure = :blood_pressure, 
                    heart_rate = :heart_rate, 
                    oxygen_level = :oxygen_level
                WHERE seniorId = :seniorId";

        $params = [
            'fName' => $fname,
            'lName' => $lname,
            'age' => $age,
            'blood_pressure' => $blood_pressure,
            'heart_rate' => $heart_rate,
            'oxygen_level' => $oxygen_level,
            'seniorId' => $seniorId
        ];
    }

    // Debugging: Print parameters to check for issues
    echo "<pre>";
    print_r($params);
    echo "</pre>";

    // Execute Query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    header("Location: seniorProfile.php");
    exit();
}
?>
