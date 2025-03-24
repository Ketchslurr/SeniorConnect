<?php
session_start();
if (!isset($_SESSION["adminId"])) {
    header("Location: login.php");
    exit();
}

require '../../config.php';

// Fetch notifications
$stmt = $pdo->query("
    SELECT n.notificationId, s.fName, s.lName, n.message, n.created_at
    FROM notifications n
    JOIN seniorcitizen s ON n.seniorId = s.seniorId
    ORDER BY n.created_at DESC
");
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

    <?php include '../../includes/adminTopbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/adminSidebar.php'; ?>

        <div class="flex-1 p-6">
            <h2 class="text-2xl font-bold text-blue-600 mb-4">Notifications</h2>

            <table class="w-full bg-white shadow-md rounded-lg">
                <thead class="bg-blue-500 text-white">
                    <tr>
                        <th class="p-3">User</th>
                        <th class="p-3">Message</th>
                        <th class="p-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notifications as $notif): ?>
                        <tr class="border-b">
                            <td class="p-3"><?= htmlspecialchars($notif['fName'] . " " . $notif['lName']); ?></td>
                            <td class="p-3"><?= htmlspecialchars($notif['message']); ?></td>
                            <td class="p-3"><?= htmlspecialchars($notif['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
