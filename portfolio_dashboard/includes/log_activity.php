<?php
// includes/log_activity.php
// Call logActivity($conn, $userId, 'type', 'description') after any tracked action.
// This now also creates a notification automatically, so no other file needs to change.

require_once __DIR__ . '/notifications.php';

function logActivity($conn, $userId, $type, $description) {
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $type, $description);
    $stmt->execute();
    $stmt->close();

    addNotification($conn, $userId, $description);
}
?>
