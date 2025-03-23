<?php
$host = "mainline.proxy.rlwy.net";
$port = "11430";
$dbname = "senior_connect";
$user = "root";
$pass = "gcBgyXeCUfVihoktdJsHDFyahPMcNvzC"; // Your Railway MySQL password

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully!";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
