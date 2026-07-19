<?php
// includes/notifications.php
// Call addNotification($conn, $userId, 'message') to create a notification.

function addNotification($conn, $userId, $message) {
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $message);
    $stmt->execute();
    $stmt->close();
}
?>
