<?php
// notifications_read.php - marks all notifications as read for the logged-in user, then redirects back
require_once 'includes/auth.php';
require_once 'includes/db.php';

$userId = $_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

$redirect = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
header("Location: $redirect");
exit;
?>
