<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$seniorId = $_SESSION['seniorId'];

// Fetch senior profile details
$sql = "SELECT * FROM seniorcitizen WHERE seniorId = :seniorId";
$stmt = $pdo->prepare($sql);
$stmt->execute(['seniorId' => $seniorId]);
$senior = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch medical records
$sqlRecords = "SELECT * FROM medical_records WHERE seniorId = :seniorId ORDER BY uploaded_at DESC";
$stmtRecords = $pdo->prepare($sqlRecords);
$stmtRecords->execute(['seniorId' => $seniorId]);
$medicalRecords = $stmtRecords->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>

        <main class="flex-1 p-6">
            <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-3xl font-bold mb-6">Senior Profile</h2>
                
                <form action="updateProfile.php" method="POST" enctype="multipart/form-data">
                    <div class="flex items-center mb-4">
                        <?php if (!empty($senior['profile_picture'])): ?>
                            <img src="../../uploads/profile_pictures/<?= htmlspecialchars($senior['profile_picture']) ?>" class="w-32 h-32 rounded-full mr-4">
                        <?php else: ?>
                            <img src="../../assets/Images/default-profile.png" class="w-32 h-32 rounded-full mr-4">
                        <?php endif; ?>
                        <input type="file" name="profile_picture" class="ml-4">
                    </div>
                    
                    <!-- <label class="block text-lg font-semibold">Name:</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($senior['fName'] . ' ' . $senior['lName']) ?>" class="w-full p-2 border rounded-lg mb-4"> -->
                    
                    <label class="block text-lg font-semibold">First Name:</label>
                    <input type="text" name="fname" value="<?= htmlspecialchars($senior['fName']) ?>" class="w-full p-2 border rounded-lg mb-4">

                    <label class="block text-lg font-semibold">Last Name:</label>
                    <input type="text" name="lname" value="<?= htmlspecialchars($senior['lName']) ?>" class="w-full p-2 border rounded-lg mb-4">

                    <label class="block text-lg font-semibold">Age:</label>
                    <input type="text" name="age" value="<?= htmlspecialchars($senior['age']) ?>" class="w-full p-2 border rounded-lg mb-4">
                    
                    <!-- <label class="block text-lg font-semibold">Contact:</label>
                    <input type="text" name="contact" value="<?= htmlspecialchars($senior['contact']) ?>" class="w-full p-2 border rounded-lg mb-4">
                    
                    <label class="block text-lg font-semibold">Address:</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($senior['address']) ?>" class="w-full p-2 border rounded-lg mb-4"> -->
                    
                    <label class="block text-lg font-semibold">Blood Pressure:</label>
                    <input type="text" name="blood_pressure" value="<?= htmlspecialchars($senior['blood_pressure'] ?? '') ?>" class="w-full p-2 border rounded-lg mb-4">

                    <label class="block text-lg font-semibold">Heart Rate:</label>
                    <input type="text" name="heart_rate" value="<?= htmlspecialchars($senior['heart_rate'] ?? '') ?>" class="w-full p-2 border rounded-lg mb-4">

                    <label class="block text-lg font-semibold">Oxygen Level:</label>
                    <input type="text" name="oxygen_level" value="<?= htmlspecialchars($senior['oxygen_level'] ?? '') ?>" class="w-full p-2 border rounded-lg mb-4">


                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Profile</button>
                </form>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md max-w-4xl mx-auto mt-6">
            <?php 
                if (isset($_SESSION['upload_status']) && !empty($_SESSION['upload_status'])): ?>
                    <div class="upload-message <?= htmlspecialchars($_SESSION['upload_status_type']); ?>">
                        <?= htmlspecialchars($_SESSION['upload_status']); ?>
                    </div>
                    <style>
                        .upload-message {
                            padding: 10px;
                            margin-bottom: 15px;
                            border-radius: 5px;
                            font-weight: bold;
                            text-align: center;
                        }

                        .success {
                            background-color: #d4edda;
                            color: #155724;
                            border: 1px solid #c3e6cb;
                        }

                        .error {
                            background-color: #f8d7da;
                            color: #721c24;
                            border: 1px solid #f5c6cb;
                        }
                    </style>
                    <?php 
                    // Clear session messages so they disappear after refresh
                    unset($_SESSION['upload_status'], $_SESSION['upload_status_type']); 
                    ?>
            <?php endif; ?>

        <h3 class="text-2xl font-semibold mb-4">Medical Records</h3>

        <!-- Upload Form -->
        <form action="../../includes/uploadMedicalRecord.php" method="POST" enctype="multipart/form-data" class="mb-4">
            <input type="file" name="medicalRecord" class="mb-2">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Upload</button>
        </form>

        <!-- Medical Records List -->
        <?php if (!empty($medicalRecords)): ?>
            <ul class="list-disc pl-6">
                <?php foreach ($medicalRecords as $record): ?>
                    <li class="mb-2">
                        <a href="#" class="text-blue-600 underline" onclick="openModal('<?= htmlspecialchars($record['file_name']) ?>')">
                            <?= htmlspecialchars($record['file_name']) ?>
                        </a> 
                        <span class="text-gray-500">(Uploaded: <?= htmlspecialchars($record['uploaded_at']) ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-600">No medical records available.</p>
        <?php endif; ?>

        <!-- Modal -->
        <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-white p-4 rounded-lg shadow-lg max-w-lg w-full relative">
                <button class="absolute top-2 right-2 text-gray-700 hover:text-gray-900" onclick="closeModal()">✖</button>
                <h4 class="text-lg font-semibold mb-2">Preview:</h4>
                <img id="modalImage" src="" class="max-w-full max-h-[500px] rounded-lg border border-gray-300">
            </div>
        </div>

        <!-- JavaScript -->
        <script>
            function openModal(fileName) {
                const imageUrl = `../../assets/uploads/medical_records/${fileName}`;
                document.getElementById("modalImage").src = imageUrl;
                document.getElementById("imageModal").classList.remove("hidden");
            }

            function closeModal() {
                document.getElementById("imageModal").classList.add("hidden");
            }
        </script>

            </div>
        </main>
    </div>
    

</body>
</html>
