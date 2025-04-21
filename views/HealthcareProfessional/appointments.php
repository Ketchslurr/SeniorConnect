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
               p.status, p.receipt, p.paymentId
        FROM appointment a
        JOIN seniorcitizen s ON a.seniorId = s.seniorId
        LEFT JOIN payments p ON a.seniorId = p.seniorId
        WHERE a.professionalId = :professionalId
        ORDER BY a.created_at desc";

$stmt = $pdo->prepare($sql);
$stmt->execute(['professionalId' => $professionalId]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Remove duplicate appointments based on appointmentId
$uniqueAppointments = [];
$seenIds = [];

foreach ($appointments as $appointment) {
    if (!in_array($appointment['appointmentId'], $seenIds)) {
        $uniqueAppointments[] = $appointment;
        $seenIds[] = $appointment['appointmentId'];
    }
}

if (empty($appointments)) {
    die("No appointments found for this doctor.");
}

// Prepare events for FullCalendar
$events = [];
foreach ($uniqueAppointments as $row) {
    $events[] = [
        'title' => " - " . htmlspecialchars($row['appointment_time']),
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
    <a href="ePrescription.php?appointmentId=<?= $appointment['appointmentId'] ?>" class="text-blue-500 hover:underline">Add Prescription</a>

    <!-- Filters -->
    <div class="flex flex-wrap mb-4 gap-4">
        <input type="text" id="searchInput" placeholder="Search..." class="border p-2 rounded w-1/4">
        
        <select id="statusFilter" class="border p-2 rounded">
            <option value="">All Status</option>
            <option value="Confirmed">Confirmed</option>
            <option value="Cancelled">Cancelled</option>
            <option value="Pending">Pending</option>
        </select>

        <select id="paymentFilter" class="border p-2 rounded">
            <option value="">All Payment Status</option>
            <option value="verified">Paid</option>
            <option value="unverified">Unpaid</option>
            <option value="pending">Pending</option>
        </select>

        <input type="date" id="startDate" class="border p-2 rounded">
        <input type="date" id="endDate" class="border p-2 rounded">
    </div>

    <!-- Scrollable Table -->
    <div class="overflow-y-auto max-h-96 border rounded-lg">
        <table class="w-full border-collapse bg-white shadow-md rounded-lg overflow-hidden">
            <thead class="sticky top-0 bg-blue-500 text-white">
                <tr>
                    <th class="py-3 px-4">Appointment No</th>
                    <th class="py-3 px-4">Senior Name</th>
                    <!-- <th class="py-3 px-4">Service</th> -->
                    <th class="py-3 px-4">Date</th>
                    <th class="py-3 px-4">Time</th>
                    <th class="py-3 px-4">Status</th>
                    <th class="py-3 px-4">Payment Status</th>
                    <th class="py-3 px-4">Receipt</th>
                    <th class="py-3 px-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="appointmentTableBody">
                <?php foreach ($uniqueAppointments as $appointment) : ?>
                    <tr class="border-b appointment-row" 
                        data-status="<?= $appointment['appointment_status'] ?>" 
                        data-payment="<?= $appointment['status'] ?>"
                        data-date="<?= $appointment['appointment_date'] ?>"
                        data-name="<?= htmlspecialchars($appointment['senior_name']) ?>">
                        <!-- <td class="py-3 px-4 text-center"><//?= htmlspecialchars($appointment['appointmentId']) ?></td> -->
                        <td class="py-3 px-4 text-center"><?= 'REF-' . str_pad($appointment['appointmentId'], 6, '0', STR_PAD_LEFT) ?></td>
                        <td class="py-3 px-4 text-center"><?= htmlspecialchars($appointment['senior_name']) ?></td>
                        <!-- <td class="py-3 px-4 text-center"><//?= htmlspecialchars($appointment['service_name']) ?></td> -->
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
                            <?php if ($appointment['status'] == 'verified'): ?>
                                <span class="text-green-600 font-bold">Paid</span>
                            <?php elseif ($appointment['status'] == 'unverified'): ?>
                                <span class="text-red-600 font-bold">Unpaid</span>
                            <?php else: ?>
                                <span class="text-yellow-600 font-bold">Pending</span>
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
                                <button onclick="openConfirmModal(<?= $appointment['appointmentId'] ?>, <?= $appointment['paymentId'] ?>)" 
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
                <input type="hidden" name="paymentId" id="confirmPaymentId">  <!-- New Hidden Field for Payment ID -->
                <div class="flex justify-center space-x-4 mt-4">
                    <button type="submit" onclick="console.log('Submitting form with:', document.getElementById('confirmAppointmentId').value, document.getElementById('confirmPaymentId').value)" 
                            class="px-4 py-2 bg-blue-600 text-white rounded">
                        Confirm
                    </button>
                    <button type="button" onclick="closeConfirmModal()" class="px-4 py-2 bg-gray-600 text-white rounded">
                        Cancel
                    </button>
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

    function openConfirmModal(appointmentId, paymentId) {
        console.log("Opening Confirm Modal for:", "Appointment ID:", appointmentId, "Payment ID:", paymentId);
        document.getElementById("confirmAppointmentId").value = appointmentId;
        document.getElementById("confirmPaymentId").value = paymentId; // Set Payment ID
        document.getElementById("confirmModal").classList.remove("hidden");
    }

    function closeConfirmModal() {
        document.getElementById("confirmModal").classList.add("hidden");
    }

    function openDenyModal(appointmentId) {
        document.getElementById('denyAppointmentId').value = appointmentId;
        document.getElementById('denyModal').classList.remove('hidden');
    }

    function closeDenyModal() {
        document.getElementById('denyModal').classList.add('hidden');
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("searchInput");
        const statusFilter = document.getElementById("statusFilter");
        const paymentFilter = document.getElementById("paymentFilter");
        const startDate = document.getElementById("startDate");
        const endDate = document.getElementById("endDate");
        const tableBody = document.getElementById("appointmentTableBody");
        const rows = document.querySelectorAll(".appointment-row");

        function filterAppointments() {
            const searchText = searchInput.value.toLowerCase();
            const selectedStatus = statusFilter.value;
            const selectedPayment = paymentFilter.value;
            const start = startDate.value ? new Date(startDate.value) : null;
            const end = endDate.value ? new Date(endDate.value) : null;

            rows.forEach(row => {
                const name = row.getAttribute("data-name").toLowerCase();
                const status = row.getAttribute("data-status");
                const payment = row.getAttribute("data-payment");
                const date = new Date(row.getAttribute("data-date"));

                let matchesSearch = name.includes(searchText);
                let matchesStatus = selectedStatus === "" || status === selectedStatus;
                let matchesPayment = selectedPayment === "" || payment === selectedPayment;
                let matchesDate = (!start || date >= start) && (!end || date <= end);

                row.style.display = (matchesSearch && matchesStatus && matchesPayment && matchesDate) ? "" : "none";
            });
        }

        searchInput.addEventListener("input", filterAppointments);
        statusFilter.addEventListener("change", filterAppointments);
        paymentFilter.addEventListener("change", filterAppointments);
        startDate.addEventListener("change", filterAppointments);
        endDate.addEventListener("change", filterAppointments);
    });
</script>

</body>
</html>
