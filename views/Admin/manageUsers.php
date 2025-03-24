<?php
session_start();
if (!isset($_SESSION["adminId"])) {
    header("Location: login.php");
    exit();
}

require '../../config.php';

$stmt = $pdo->query("SELECT userId, fName, lName, email, roleId FROM user_info ORDER BY roleId DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <?php include '../../includes/adminTopbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/adminSidebar.php'; ?>

        <div class="flex-1 p-6">
            <h2 class="text-2xl font-bold text-blue-600 mb-4">Manage Users</h2>

            <!-- Search bar -->
            <input type="text" id="search" placeholder="Search users..." 
                class="p-2 border rounded-md w-full mb-4" 
                onkeyup="searchUsers()">

            <!-- Users Table -->
            <table class="w-full bg-white shadow-md rounded-lg">
                <thead class="bg-blue-500 text-white">
                    <tr>
                        <th class="p-3">User ID</th>
                        <th class="p-3">Full Name</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Role</th>
                        <th class="p-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="userTable">
                    <?php foreach ($users as $user): ?>
                        <tr class="border-b">
                            <td class="p-3"><?= htmlspecialchars($user['userId']); ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['fName'] .''. $user['lName'] ?? 'N/A'); ?></td>
                            <td class="p-3"><?= htmlspecialchars($user['email']); ?></td>
                            <td class="p-3 font-semibold <?= ($user['roleId'] == 2) ? 'text-green-600' : 'text-blue-600' ?>">
                                <?= ($user['roleId'] == 2) ? 'Senior' : 'Doctor'; ?>
                            </td>
                            <td class="p-3">
                                <a href="edit_user.php?id=<?= $user['userId']; ?>" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-700">Edit</a>
                                <a href="delete_user.php?id=<?= $user['userId']; ?>" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-700" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function searchUsers() {
        let input = document.getElementById("search").value.toLowerCase();
        let rows = document.querySelectorAll("#userTable tr");

        rows.forEach(row => {
            let name = row.cells[1].innerText.toLowerCase();
            let email = row.cells[2].innerText.toLowerCase();
            row.style.display = (name.includes(input) || email.includes(input)) ? "" : "none";
        });
    }
    </script>

</body>
</html>
