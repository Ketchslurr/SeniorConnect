<?php
include '../../config.php';
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: ../../login.php");
    exit();
}

$professionalId = $_SESSION['professionalId'];

// Get doctor name
$stmt = $pdo->prepare("SELECT fname, lname FROM healthcareprofessional WHERE professionalId = ?");
$stmt->execute([$professionalId]);
$doctor = $stmt->fetch();
$doctorName = $doctor ? $doctor['fname'] . ' ' . $doctor['lname'] : '';

// Get prescriptions written by the doctor
$prescriptions = $pdo->prepare("
    SELECT ep.*, sc.fname AS patientName
    FROM prescriptions ep
    JOIN appointment a ON ep.appointmentId = a.appointmentId
    JOIN seniorcitizen sc ON a.seniorId = sc.seniorId
    WHERE a.professionalId = ? AND ep.deleted_at IS Null
    ORDER BY ep.created_at DESC
");
$prescriptions->execute([$professionalId]);
$prescriptions = $prescriptions->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Prescription</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include '../../includes/topbar.php'; ?>
<div class="flex">
    <?php include '../../includes/healthcareProfessionalSidebar.php'; ?>

    <div class="flex-1 p-6 overflow-y-auto custom-scroll" style="max-height: calc(100vh - 4rem);">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">E-Prescription</h2>

        <!-- Add Prescription -->
        <div class="bg-white p-6 shadow-md rounded-lg mb-6">
            <h3 class="text-xl font-semibold mb-4">Add Prescription</h3>
            <form action="savePrescription.php" method="POST" id="prescriptionForm">
                <input type="hidden" name="appointmentId" id="appointmentId">

                <div class="mb-4">
                    <label class="block text-sm font-medium">Name of Doctor</label>
                    <input type="text" readonly name="doctorName" id="doctorName" value="<?= htmlspecialchars($doctorName) ?>"
                           class="bg-gray-100 mt-1 block w-full rounded-md shadow-sm sm:text-sm border-gray-300 cursor-not-allowed">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Reference No:</label>
                    <div class="flex gap-2">
                        <input type="text" id="referenceNo" class="block w-full sm:text-sm border-gray-300 rounded-md shadow-sm"
                               onblur="fetchAppointmentDetails()" required>
                        <button type="button" onclick="fetchAppointmentDetails()" class="bg-blue-500 text-white px-4 py-1 rounded">Fetch</button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Name of Patient</label>
                    <input type="text" id="patientName" name="patientName"
                           class="block w-full sm:text-sm border-gray-300 rounded-md shadow-sm" readonly>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Date</label>
                    <input type="datetime-local" id="date" name="date"
                           class="block w-full sm:text-sm border-gray-300 rounded-md shadow-sm" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Prescription</label>
                    <!-- <textarea name="prescription" rows="3" required
                              class="block w-full sm:text-sm border-gray-300 rounded-md shadow-sm"></textarea> -->
                    <textarea name="prescription_text" rows="3" required
                    class="block w-full sm:text-sm border-gray-300 rounded-md shadow-sm"></textarea>

                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium">Instruction of Doctor</label>
                    <textarea name="instructions" rows="3" required
                              class="block w-full sm:text-sm border-gray-300 rounded-md shadow-sm"></textarea>
                </div>

                <div>
                    <button type="submit"
                            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">Save</button>
                </div>
            </form>
        </div>

        <!-- Prescription List -->
        <div class="bg-white p-6 shadow-md rounded-lg">
            <h3 class="text-xl font-semibold text-gray-700 mb-4">Prescription Details</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-200">
                    <thead class="bg-purple-600 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Doctor</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Patient</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Date</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Prescription</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Instructions</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($prescriptions as $rx): ?>
                        <tr class="border-t">
                            <td class="px-6 py-4 text-sm"><?= htmlspecialchars($doctorName) ?></td>
                            <td class="px-6 py-4 text-sm"><?= htmlspecialchars($rx['patientName']) ?></td>
                            <td class="px-6 py-4 text-sm"><?= htmlspecialchars($rx['created_at']) ?></td>
                            <td class="px-6 py-4 text-sm"><?= htmlspecialchars($rx['prescription_text']) ?></td>
                            <td class="px-6 py-4 text-sm"><?= htmlspecialchars($rx['instructions']) ?></td>
                            <td class="px-6 py-4 text-sm">
                                <a href="editPrescription.php?id=<?= $rx['prescriptionId'] ?>" class="text-blue-600 hover:underline">Edit</a>
                                <a href="sendPrescription.php?id=<?= $rx['prescriptionId'] ?>" class="text-green-600 ml-4 hover:underline">Send</a>
                                <!-- <a href="deletePrescription.php?id=<//?= $rx['prescriptionId'] ?>" class="text-red-600 ml-4 hover:underline" onclick="return confirm('Are you sure?')">Delete</a> -->
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal<?= $prescription['id'] ?>">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($prescriptions)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-gray-500">No prescriptions found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function fetchAppointmentDetails() {
    const ref = document.getElementById("referenceNo").value.trim();
    if (!ref) return;

    fetch(`getAppointmentDetails.php?ref=${encodeURIComponent(ref)}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }

            document.getElementById("patientName").value = data.patientName || '';
            document.getElementById("appointmentId").value = data.appointmentId || '';
            document.getElementById("date").value = data.appointment_date
                ? new Date(data.appointment_date).toISOString().slice(0, 16)
                : '';
        })
        .catch(err => {
            console.error(err);
            alert("Something went wrong.");
        });
}
</script>
</body>
</html>
