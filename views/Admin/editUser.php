<?php
session_start();
if (!isset($_SESSION["adminId"])) {
    header("Location: login.php");
    exit();
}

require '../../config.php';

if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$userId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM user_info WHERE userId = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: manage_users.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $roleId = $_POST['roleId'];

    $updateStmt = $pdo->prepare("UPDATE user_info SET fullname = ?, email = ?, roleId = ? WHERE userId = ?");
    $updateStmt->execute([$fullname, $email, $roleId, $userId]);

    header("Location: manage_users.php");
    exit();
}
?>

<?php include 'admin_header.php'; ?>

<div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold text-blue-600">Edit User</h2>

    <form method="POST" class="bg-white p-4 shadow-md rounded-md">
        <label class="block font-semibold">Full Name:</label>
        <input type="text" name="fullname" value="<?= $user['fullname'] ?>" required class="p-2 border rounded-md w-full">

        <label class="block font-semibold mt-3">Email:</label>
        <input type="email" name="email" value="<?= $user['email'] ?>" required class="p-2 border rounded-md w-full">

        <label class="block font-semibold mt-3">Role:</label>
        <select name="roleId" class="p-2 border rounded-md w-full">
            <option value="2" <?= $user['roleId'] == 2 ? 'selected' : '' ?>>Senior</option>
            <option value="3" <?= $user['roleId'] == 3 ? 'selected' : '' ?>>Doctor</option>
        </select>

        <button type="submit" class="mt-4 p-2 bg-blue-600 text-white rounded-md w-full">Update</button>
    </form>
</div>

<?php include 'admin_footer.php'; ?>
