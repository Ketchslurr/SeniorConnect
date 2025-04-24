<?php
require '../../vendor/autoload.php';
use Dompdf\Dompdf;

session_start();
if (!isset($_SESSION['userId'])) {
    die("Unauthorized.");
}

include '../../config.php';
$userId = $_SESSION['userId'];

$stmt = $pdo->prepare("SELECT * FROM health_data WHERE userId = ? ORDER BY timestamp DESC LIMIT 1");
$stmt->execute([$userId]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$html = '<h2>🩺 Health Report</h2>';
if ($data) {
    $html .= "
        <p><strong>Date:</strong> {$data['timestamp']}</p>
        <p><strong>Heart Rate:</strong> {$data['bpm']} bpm</p>
        <p><strong>Steps:</strong> {$data['steps']} steps</p>
        <p><strong>Calories:</strong> {$data['calories']} kcal</p>
    ";
} else {
    $html .= "<p>No data found.</p>";
}

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("Health_Report.pdf", ["Attachment" => 0]);
