<?php
// contact_submit.php — handles contact form POST from preview.php
// Saves message to DB, returns JSON response

header('Content-Type: application/json');

require_once 'includes/db.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$message = trim($_POST['message'] ?? '');

// Server-side validation
$errors = [];
if ($name === '')                          $errors[] = 'Name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if ($message === '')                       $errors[] = 'Message is required.';
if (strlen($name) > 100)                  $errors[] = 'Name too long.';
if (strlen($message) > 3000)             $errors[] = 'Message too long.';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Get the first (and only) admin user id
$userRow = $conn->query("SELECT id FROM users LIMIT 1")->fetch_assoc();
if (!$userRow) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error.']);
    exit;
}
$userId = $userRow['id'];

$stmt = $conn->prepare("INSERT INTO messages (user_id, sender_name, sender_email, message) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $userId, $name, $email, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message sent! I will get back to you soon.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save message. Please try again.']);
}
$stmt->close();
