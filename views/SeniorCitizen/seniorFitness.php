<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) { 
    header("Location: ../../login.php");
    exit();
}

$seniorId = $_SESSION['seniorId'];

// Fetch available fitness classes
$sql = "SELECT * FROM fitness_classes";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch purchased fitness classes
$sqlPurchased = "SELECT fitnessId FROM fitness_purchases WHERE seniorId = :seniorId AND payment_status = 'Completed'";
$stmtPurchased = $pdo->prepare($sqlPurchased);
$stmtPurchased->execute(['seniorId' => $seniorId]);
$purchasedClasses = $stmtPurchased->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Fitness Classes</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    
    <div class="flex">
        <!-- Sidebar -->
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Fitness Classes</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($classes as $class) : ?>
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="relative w-full h-48">
                            <video class="w-full h-full object-cover" controls>
                                <source src="../../<?= htmlspecialchars($class['video_url']) ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-semibold"><?= htmlspecialchars($class['title']) ?></h3>
                            <p class="text-gray-600 text-sm"><?= htmlspecialchars($class['description']) ?></p>
                            <p class="text-blue-600 font-semibold mt-2">
                                <?= $class['price'] == 0 ? "Free" : "₱" . $class['price'] ?>
                            </p>

                            <div class="mt-4">
                                <?php if ($class['price'] == 0) : ?>
                                    <a href="watch.php?fitnessId=<?= $class['fitnessId'] ?>" class="block bg-green-500 text-white text-center px-4 py-2 rounded hover:bg-green-600 transition">
                                        Watch Now
                                    </a>
                                <?php elseif (in_array($class['fitnessId'], $purchasedClasses)) : ?>
                                    <p class="text-green-600 font-semibold text-center">✔ You have already purchased this video</p>
                                    <a href="watch.php?fitnessId=<?= $class['fitnessId'] ?>" class="block bg-green-500 text-white text-center px-4 py-2 rounded hover:bg-green-600 transition mt-2">
                                        Watch Now
                                    </a>
                                <?php else : ?>
                                    <form action="purchase.php" method="POST">
                                        <input type="hidden" name="fitnessId" value="<?= $class['fitnessId'] ?>">
                                        <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">
                                            Purchase & Watch
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($classes)) : ?>
                    <p class="text-center text-gray-500 p-4 col-span-full">No fitness classes available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
