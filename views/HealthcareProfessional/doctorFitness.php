<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) { 
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $doctorId = $_SESSION['professionalId'];

    // Handle file upload
    $targetDir = "../../uploads/";
    $fileName = basename($_FILES["video"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $videoUrl = "uploads/" . $fileName;

    if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetFilePath)) {
        $sql = "INSERT INTO fitness_classes (professionalId, title, description, video_url, price) 
                VALUES (:doctorId, :title, :description, :video_url, :price)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([
            'doctorId' => $doctorId,
            'title' => $title,
            'description' => $description,
            'video_url' => $videoUrl,
            'price' => $price
        ])) {
            $_SESSION['success'] = "Fitness class uploaded successfully.";
        } else {
            $_SESSION['error'] = "Database error occurred.";
        }
    } else {
        $_SESSION['error'] = "Error uploading video.";
    }

    header("Location: doctorFitness.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Fitness Class</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include '../../includes/topbar.php'; ?>
<div class="flex">
    <!-- Sidebar -->
    <?php include '../../includes/healthcareProfessionalSidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 p-6">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Upload Fitness Class</h2>

        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 shadow-md rounded-lg">
            <form action="doctorFitness.php" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Class Title</label>
                    <input type="text" name="title" class="w-full border-gray-300 rounded-lg p-2" placeholder="Enter class title" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Class Description</label>
                    <textarea name="description" class="w-full border-gray-300 rounded-lg p-2" placeholder="Describe the class" required></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Price (Enter 0 for Free)</label>
                    <input type="number" step="0.01" name="price" class="w-full border-gray-300 rounded-lg p-2" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Upload Video</label>
                    <input type="file" name="video" accept="video/*" class="w-full border-gray-300 rounded-lg p-2" required>
                </div>

                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition">Upload</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
