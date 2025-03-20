<?php
include '../../config.php';

function createNotification($pdo, $seniorId, $type, $appointmentId = null) {
    // Define message templates
    $messages = [
        'accepted' => "Your appointment (ID: {$appointmentId}) has been accepted.",
        'declined' => "Your appointment (ID: {$appointmentId}) has been declined.",
        'booked' => "Your appointment (ID: {$appointmentId}) has been successfully booked.",
        'general' => "You have a new notification."
    ];

    // Use the appropriate message template
    $message = isset($messages[$type]) ? $messages[$type] : $messages['general'];

    // Insert into notifications table
    $sql = "INSERT INTO notifications (seniorId, message, type, is_read) VALUES (:seniorId, :message, :type, 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'seniorId' => $seniorId,
        'message' => $message,
        'type' => $type
    ]);
}
?>
