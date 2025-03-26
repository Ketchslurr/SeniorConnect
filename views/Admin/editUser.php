<?php
session_start();
if (!isset($_SESSION["adminId"])) {
    header("Location: login.php");
    exit();
}

require '../../config.php';

if (!isset($_GET['id'])) {
    header("Location: manageUsers.php");
    exit();
}

$userId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM user_info WHERE userId = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error'] = "User not found.";
    header("Location: manageUsers.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fName = $_POST['fName'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $roleId = $_POST['roleId'];

    $updateStmt = $pdo->prepare("UPDATE user_info SET fName = ?, lname = ?, email = ?, roleId = ? WHERE userId = ?");
    $updateStmt->execute([$fName, $lname, $email, $roleId, $userId]);

    $_SESSION['success'] = "User details updated successfully.";
    header("Location: manageUsers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">

    <!--  Include Topbar -->
    <?php include '../../includes/topbar.php'; ?>

    <div class="flex">
        <!--  Include Sidebar -->
        <?php include '../../includes/adminSidebar.php'; ?>

        <div class="flex-1 p-6">
            <h1 class="text-2xl font-semibold text-gray-800 mb-6">Edit User</h1>

            <div class="bg-white shadow-md rounded-lg p-6">
                <!-- Error Message -->
                <?php if (isset($_SESSION['error'])) : ?>
                    <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!--  Success Message -->
                <?php if (isset($_SESSION['success'])) : ?>
                    <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-semibold">First Name:</label>
                        <input type="text" name="fName" value="<?= htmlspecialchars($user['fName'] ?? '') ?>" required class="w-full p-2 border rounded-md">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold">Last Name:</label>
                        <input type="text" name="lname" value="<?= htmlspecialchars($user['lname'] ?? '') ?>" required class="w-full p-2 border rounded-md">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold">Email:</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required class="w-full p-2 border rounded-md">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-semibold">Role:</label>
                        <select name="roleId" class="w-full p-2 border rounded-md">
                            <option value="2" <?= isset($user['roleId']) && $user['roleId'] == 2 ? 'selected' : '' ?>>Senior</option>
                            <option value="3" <?= isset($user['roleId']) && $user['roleId'] == 3 ? 'selected' : '' ?>>Doctor</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-md transition duration-200">
                        Update User
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
