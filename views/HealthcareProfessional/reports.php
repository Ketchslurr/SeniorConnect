<?php
include '../../config.php';
// require_once '../../tcpdf/tcpdf.php';
include '../../vendor/autoload.php';
use TCPDF;
session_start();

if (!isset($_SESSION['userId'])) {
    header("Location: login.php");
    exit();
}

$appointments = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fromDate = $_POST['from_date'] ?? '';
    $toDate = $_POST['to_date'] ?? '';
    
    if (!empty($fromDate) && !empty($toDate)) {
        $sql = "SELECT a.appointmentId, a.date, a.time, a.appointment_status, 
                       hp.fname AS doctor_fname, hp.lname AS doctor_lname,
                       p.fname AS patient_fname, p.lname AS patient_lname
                FROM appointments a
                JOIN healthcareprofessional hp ON a.professionalId = hp.professionalId
                JOIN patients p ON a.patientId = p.patientId
                WHERE a.date BETWEEN :fromDate AND :toDate";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['fromDate' => $fromDate, 'toDate' => $toDate]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

if (isset($_POST['generate_pdf'])) {
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Appointment Reports', 0, 1, 'C');
    
    $pdf->Cell(40, 10, 'Date', 1);
    $pdf->Cell(40, 10, 'Doctor', 1);
    $pdf->Cell(40, 10, 'Patient', 1);
    $pdf->Cell(40, 10, 'Status', 1);
    $pdf->Ln();
    
    foreach ($appointments as $appointment) {
        $pdf->Cell(40, 10, $appointment['date'], 1);
        $pdf->Cell(40, 10, $appointment['doctor_fname'] . ' ' . $appointment['doctor_lname'], 1);
        $pdf->Cell(40, 10, $appointment['patient_fname'] . ' ' . $appointment['patient_lname'], 1);
        $pdf->Cell(40, 10, $appointment['appointment_status'], 1);
        $pdf->Ln();
    }
    
    $pdf->Output('Appointment_Report.pdf', 'D');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include '../../includes/topbar.php'; ?>
    <div class="flex">
        <?php include '../../includes/seniorCitizenSidebar.php'; ?>
        
        <main class="flex-1 p-6">
            <h2 class="text-3xl font-bold text-center mb-6">Appointment Reports</h2>
            
            <form method="POST" class="mb-6 flex flex-col md:flex-row gap-4 items-center">
                <input type="date" name="from_date" required class="px-4 py-2 border rounded-lg">
                <input type="date" name="to_date" required class="px-4 py-2 border rounded-lg">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Filter
                </button>
                <button type="submit" name="generate_pdf" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Download PDF
                </button>
            </form>
            
            <table class="min-w-full bg-white rounded-lg shadow-md">
                <thead>
                    <tr>
                        <th class="py-3 px-4 border">Date</th>
                        <th class="py-3 px-4 border">Doctor</th>
                        <th class="py-3 px-4 border">Patient</th>
                        <th class="py-3 px-4 border">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)): ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td class="py-3 px-4 border text-center"> <?= htmlspecialchars($appointment['date']) ?> </td>
                                <td class="py-3 px-4 border text-center"> <?= htmlspecialchars($appointment['doctor_fname'] . ' ' . $appointment['doctor_lname']) ?> </td>
                                <td class="py-3 px-4 border text-center"> <?= htmlspecialchars($appointment['patient_fname'] . ' ' . $appointment['patient_lname']) ?> </td>
                                <td class="py-3 px-4 border text-center"> <?= htmlspecialchars($appointment['appointment_status']) ?> </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="py-3 px-4 border text-center text-gray-500">No appointments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
