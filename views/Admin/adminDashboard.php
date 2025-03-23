<?php
session_start();
if (!isset($_SESSION["adminId"])) {
    header("Location: login.php");
    exit();
}

require '../../config.php';

// Get appointment count
$totalAppointments = $pdo->query("SELECT COUNT(*) FROM appointment")->fetchColumn();

// Get user count
$totalUsers = $pdo->query("SELECT COUNT(*) FROM user_info")->fetchColumn();

// Get professionals count
$totalProfessionals = $pdo->query("SELECT COUNT(*) FROM healthcareprofessional")->fetchColumn();
?>

<h2>Admin Dashboard</h2>
<p>Total Appointments: <?= $totalAppointments ?></p>
<p>Total Users: <?= $totalUsers ?></p>
<p>Total Professionals: <?= $totalProfessionals ?></p>

<a href="manage_appointments.php">Manage Appointments</a>
<a href="manage_users.php">Manage Users</a>
<a href="logout.php">Logout</a>
