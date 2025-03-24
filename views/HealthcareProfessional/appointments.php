<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['professionalId'])) {
    die("Error: Professional ID not set in session.");
}

// Get doctor's ID
$professionalId = $_SESSION['professionalId'];

// Function to create a notification
function createNotification($pdo, $seniorId, $message) {
    $sql = "INSERT INTO notifications (seniorId, message, created_at) VALUES (:seniorId, :message, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'seniorId' => $seniorId,
        'message' => $message
    ]);
}

// Fetch appointments booked with the doctor
$sql = "SELECT a.*, s.fname AS senior_name, 
               p.status, p.receipt
        FROM appointment a
        JOIN seniorcitizen s ON a.seniorId = s.seniorId
        LEFT JOIN payments p ON a.seniorId = p.seniorId
        WHERE a.professionalId = :professionalId
        ORDER BY a.appointment_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute(['professionalId' => $professionalId]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($appointments)) {
    die("No appointments found for this doctor.");
}

// Prepare events for FullCalendar
$events = [];
foreach ($appointments as $row) {
    $events[] = [
        'title' => htmlspecialchars($row['service_name']) . " - " . htmlspecialchars($row['appointment_time']),
        'start' => htmlspecialchars($row['appointment_date']),
        'id' => htmlspecialchars($row['appointmentId']),
        'color' => $row['appointment_status'] == 'Confirmed' ? '#007bff' : ($row['appointment_status'] == 'Cancelled' ? '#dc3545' : 'grey'),
    ];
}

// Handle appointment status change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointmentId']) && isset($_POST['status'])) {
    $appointmentId = $_POST['appointmentId'];
    $newStatus = $_POST['status'];

    // Update the appointment status in the database
    $updateSql = "UPDATE appointment SET appointment_status = :status WHERE appointmentId = :appointmentId";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
        'status' => $newStatus,
        'appointmentId' => $appointmentId
    ]);

    // Fetch the senior ID for the appointment
    $seniorSql = "SELECT seniorId FROM appointment WHERE appointmentId = :appointmentId";
    $seniorStmt = $pdo->prepare($seniorSql);
    $seniorStmt->execute(['appointmentId' => $appointmentId]);
    $senior = $seniorStmt->fetch(PDO::FETCH_ASSOC);

    if ($senior) {
        $seniorId = $senior['seniorId'];
        $message = ($newStatus == 'Confirmed') 
            ? "Your appointment has been confirmed by the doctor." 
            : "Your appointment has been declined by the doctor.";
        
        // Create notification
        createNotification($pdo, $seniorId, $message);
    }

    echo json_encode(['status' => 'success', 'message' => 'Appointment updated successfully']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js"></script>
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/healthcareProfessionalSidebar.php'; ?>
        <main class="flex-1 p-6">
            <h3 class="text-3xl font-bold mb-6 text-center">Appointments</h3>
            
            <!-- List View -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h4 class="text-xl font-bold mb-4">Appointment List</h4>
                <table class="w-full border-collapse bg-white shadow-md rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-blue-500 text-white">
                            <th class="py-3 px-4">Senior Name</th>
                            <th class="py-3 px-4">Service</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Time</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Payment Status</th>
                            <th class="py-3 px-4">Receipt</th>
                            <th class="py-3 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment) : ?>
                            <tr class="border-b">
                                <td class="py-3 px-4 text-center"><?= htmlspecialchars($appointment['senior_name']) ?></td>
                                <td class="py-3 px-4 text-center"><?= htmlspecialchars($appointment['service_name']) ?></td>
                                <td class="py-3 px-4 text-center"><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                                <td class="py-3 px-4 text-center"><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                                <td class="py-3 px-4 text-center">
                                <?php if ($appointment['appointment_status'] == 'Confirmed'): ?>
                                    <span class="text-blue-600 font-bold">Confirmed</span>
                                <?php elseif ($appointment['appointment_status'] == 'Cancelled'): ?>
                                    <span class="text-red-600 font-bold">Cancelled</span>
                                <?php else: ?>
                                    <span class="text-black-600 font-bold">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if (!empty($appointment['receipt'])): ?>
                                    <a href="#" onclick="openModal('<?= htmlspecialchars($appointment['receipt']) ?>')" class="text-blue-500 underline">
                                        View Receipt
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-500">No Receipt</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($appointment['appointment_status'] == 'Pending'): ?>
                                    <button onclick="openConfirmModal(<?= $appointment['appointmentId'] ?>)" 
                                        class="text-blue-500 text-xl mx-2 cursor-pointer" 
                                        title="Confirm Appointment">
                                            ✔️
                                    </button>
                                    <button onclick="openDenyModal(<?= $appointment['appointmentId'] ?>)" 
                                        class="text-red-500 text-xl mx-2 cursor-pointer" 
                                        title="Deny Appointment">
                                            ❌
                                    </button>
                                <?php endif; ?>
                            </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>

            <!-- Calendar View -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h4 class="text-xl font-bold mb-4">Appointment Calendar</h4>
                <div id="calendar"></div>
            </div>
        </main>
    </div>

    <!-- Receipt Modal  -->
    <div id="receiptModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg relative">
            <span class="absolute top-2 right-4 text-2xl cursor-pointer" onclick="closeModal()">&times;</span>
            <h2 class="text-xl font-bold text-center mb-4">Payment Receipt</h2>
            <img id="receiptImage" src="" alt="Receipt" class="w-full rounded-lg">
        </div>
    </div>

    <!-- Confirm Modal -->
    
    <div id="confirmModal" class="fixed inset-0 hidden bg-gray-900 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h3 class="text-xl font-bold mb-4">Confirm Appointment</h3>
            <p>Are you sure you want to confirm this appointment?</p>
            <form id="confirmForm" method="POST" action="../../includes/confirmAppointment.php">
                <input type="hidden" name="appointmentId" id="confirmAppointmentId">
                <div class="flex justify-center space-x-4 mt-4">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Confirm</button>
                    <button type="button" onclick="closeConfirmModal()" class="px-4 py-2 bg-gray-600 text-white rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>



    <!-- Deny Modal -->
    
    <div id="denyModal" class="fixed inset-0 hidden bg-gray-900 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h3 class="text-xl font-bold mb-4">Deny Appointment</h3>
            <form id="denyForm" method="POST" action="../../includes/denyAppointment.php">
                <input type="hidden" name="appointmentId" id="denyAppointmentId">
                <textarea name="denialReason" id="denyMessage" class="w-full p-2 border border-gray-300 rounded" placeholder="Enter reason for denial..." required></textarea>
                <div class="flex justify-center space-x-4 mt-4">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Submit</button>
                    <button type="button" onclick="closeDenyModal()" class="px-4 py-2 bg-gray-600 text-white rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    function openModal(receiptPath) {
        const modal = document.getElementById('receiptModal');
        const img = document.getElementById('receiptImage');
        img.src = "../../assets/uploads/payments/" + receiptPath; 
        modal.classList.remove('hidden');

        // Close modal when clicking outside the image
        modal.addEventListener("click", function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    }

    function closeModal() {
        document.getElementById('receiptModal').classList.add('hidden');
    }
</script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'en',
            height: 'auto',
            events: <?= json_encode($events) ?>,
        });
        calendar.render();
    });

    function openConfirmModal(appointmentId) {
        document.getElementById('confirmAppointmentId').value = appointmentId;
        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
    }

    function openDenyModal(appointmentId) {
        document.getElementById('denyAppointmentId').value = appointmentId;
        document.getElementById('denyModal').classList.remove('hidden');
    }

    function closeDenyModal() {
        document.getElementById('denyModal').classList.add('hidden');
    }
</script>


</body>
</html>
