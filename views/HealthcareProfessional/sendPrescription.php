<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: ../../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$prescriptionId = $_GET['id'];

// Fetch prescription + related info
$sql = "
    SELECT p.prescriptionId, p.prescription_text, p.instructions, a.seniorId, sc.fname, sc.lname
    FROM prescriptions p
    JOIN appointment a ON p.appointmentId = a.appointmentId
    JOIN seniorcitizen sc ON a.seniorId = sc.seniorId
    WHERE p.prescriptionId = :id
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $prescriptionId]);
$prescription = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prescription) {
    die("Prescription not found.");
}

// Create notification
$seniorId = $prescription['seniorId'];
$message = "You have received a new prescription from your doctor.";

$notifSql = "INSERT INTO notifications (seniorId, message, created_at) VALUES (:seniorId, :message, NOW())";
$notifStmt = $pdo->prepare($notifSql);
$notifStmt->execute([
    'seniorId' => $seniorId,
    'message' => $message
]);

// Optionally: Mark as sent in `prescriptions` table (if such column exists)
// $pdo->prepare("UPDATE prescriptions SET sent = 1 WHERE prescriptionId = ?")->execute([$prescriptionId]);

// Redirect back to ePrescription.php
header("Location: ePrescription.php?sent=1");
exit();

// include '../../config.php';
// session_start();

// if (!isset($_SESSION['professionalId'])) {
//     die("Unauthorized");
// }

// $prescriptionId = $_POST['prescriptionId'] ?? null;

// // if (!$prescriptionId) {
// //     die("Missing prescription ID.");
// // }

// try {
//     // Optional: add a `sent_at` timestamp or `status` column in your `prescriptions` table.
//     $stmt = $pdo->prepare("UPDATE prescriptions SET status = 'sent', sent_at = NOW() WHERE id = ?");
//     $stmt->execute([$prescriptionId]);

//     // Optional: notify senior citizen using a `notifications` table
//     // You'll need to join appointments to find the senior's ID
//     $notifStmt = $pdo->prepare("
//         INSERT INTO notifications (user_id, message, created_at)
//         SELECT a.seniorId, 'Your prescription has been sent.', NOW()
//         FROM prescriptions p
//         JOIN appointments a ON p.appointmentId = a.id
//         WHERE p.id = ?
//     ");
//     $notifStmt->execute([$prescriptionId]);

//     header("Location: ePrescription.php?sent=1");
//     exit();
// } catch (PDOException $e) {
//     echo "Error: " . $e->getMessage();
// }
?>
