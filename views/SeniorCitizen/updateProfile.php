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

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
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

            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
                throw new Exception("Error uploading profile picture.");
            }
        } else {
            $fileName = $senior['profile_picture']; // Use existing picture if no new file is uploaded
        }

        // Check if profile_picture column exists
        $checkColumnSQL = "SHOW COLUMNS FROM seniorcitizen LIKE 'profile_picture'";
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

        // Execute Query
        $stmt = $pdo->prepare($sql);
        if (!$stmt->execute($params)) {
            throw new Exception("Error updating profile.");
        }

        header("Location: seniorProfile.php");
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

<?php if (!empty($error)) : ?>
<!-- Modal -->
<div class="modal fade show" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true" style="display: block; background: rgba(0,0,0,0.5);">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="errorModalLabel">Error</h5>
                <button type="button" class="btn-close" onclick="closeModal()" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= htmlspecialchars($error) ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function closeModal() {
        window.location.href = 'seniorProfile.php'; // Redirect after closing modal
    }
</script>
<?php endif; ?>

<!-- Your Profile Update Form Here -->

</body>
</html>
