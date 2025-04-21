<?php
include '../../config.php';
define('APP_SECRET_KEY', '8c9f1a2d59b1c0e5aa33d8e5ef3b7c19'); 
function decryptPaymentId($encryptedId, $key) {
    $cipher = "AES-128-CTR";
    $iv = '1234567891011121';
    return openssl_decrypt(hex2bin($encryptedId), $cipher, $key, 0, $iv);
}

$encrypted = $_GET['invoice'] ?? null;
if (!$encrypted) {
    die("Invalid invoice.");
}

$paymentId = decryptPaymentId($encrypted, APP_SECRET_KEY);

// Fetch and display invoice based on $paymentId
$stmt = $pdo->prepare("SELECT * FROM payments WHERE paymentId = :id");
$stmt->execute([':id' => $paymentId]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("Invoice not found.");
}

// Display the invoice...
echo "<h1>Invoice #$paymentId</h1>";
echo "<p>Status: {$payment['status']}</p>";
echo "<p>Amount: ₱{$payment['amount']}</p>";
echo "<p>Payment Date: {$payment['paymentDate']}</p>";
?>
