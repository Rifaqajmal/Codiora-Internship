<?php
// includes/auth.php - Session/auth guard. Include at the very top of protected pages.
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
