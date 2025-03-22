<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$seniorId = $_SESSION['seniorId'];

// Fetch notifications for this senior
$sql = "SELECT * FROM notifications WHERE seniorId = :seniorId ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['seniorId' => $seniorId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>

        <main class="flex-1 p-6">
            <h2 class="text-3xl font-bold text-center mb-6">Notifications</h2>
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-2xl font-bold mb-4">Recent Updates</h3>
                <div class="space-y-4">
                    <?php if (!empty($notifications)) {
                        foreach ($notifications as $notif) { ?>
                            <a href="<?= htmlspecialchars($notif['link']) ?>" target="_blank" class="block border-l-4 border-blue-500 bg-blue-100 p-4 rounded-lg hover:bg-blue-200 transition">
                                <p class="font-semibold"><?= htmlspecialchars($notif['message']) ?></p>
                                <p class="text-sm text-gray-600">Received on: <?= htmlspecialchars($notif['created_at']) ?></p>
                            </a>
                        <?php }
                    } else { ?>
                        <div class="border-l-4 border-gray-500 bg-gray-100 p-4">
                            <p>No new notifications.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
