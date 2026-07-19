<?php
// setup.php - RUN THIS ONCE in the browser after importing database.sql
// It creates the admin user with a proper password hash, plus sample profile/category data.
// After running, delete this file or it will keep re-seeding duplicate categories.

require_once 'includes/db.php';

$messages = [];

// 1. Create admin user if not exists
$check = $conn->query("SELECT id FROM users WHERE username = 'admin'");
if ($check->num_rows === 0) {
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $username = 'admin';
    $email = 'admin@example.com';
    $stmt->bind_param("sss", $username, $hash, $email);
    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();
    $messages[] = "Admin user created. Username: admin | Password: admin123";
} else {
    $row = $check->fetch_assoc();
    $userId = $row['id'];
    $messages[] = "Admin user already exists.";
}

// 2. Seed profile if not exists
$check = $conn->query("SELECT id FROM profile WHERE user_id = $userId");
if ($check->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO profile (user_id, full_name, job_title, email, phone, location, about_text) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $fullName = "Rifaq Ajmal";
    $jobTitle = "Full Stack Developer";
    $pemail = "admin@example.com";
    $phone = "03000000000";
    $location = "Mardan, Pakistan";
    $about = "I am a Computer Science student passionate about building full-stack web applications using PHP, MySQL, and modern frontend tools.";
    $stmt->bind_param("issssss", $userId, $fullName, $jobTitle, $pemail, $phone, $location, $about);
    $stmt->execute();
    $stmt->close();
    $messages[] = "Default profile created.";
} else {
    $messages[] = "Profile already exists.";
}

// 3. Seed sample categories if none exist
$check = $conn->query("SELECT id FROM categories WHERE user_id = $userId");
if ($check->num_rows === 0) {
    $cats = ['Web Development', 'Mobile App', 'University Project'];
    $stmt = $conn->prepare("INSERT INTO categories (user_id, category_name) VALUES (?, ?)");
    foreach ($cats as $c) {
        $stmt->bind_param("is", $userId, $c);
        $stmt->execute();
    }
    $stmt->close();
    $messages[] = "Sample categories created.";
} else {
    $messages[] = "Categories already exist.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm" style="max-width:600px;margin:auto;">
        <div class="card-body">
            <h3 class="mb-3">Setup Complete</h3>
            <ul class="list-group mb-3">
                <?php foreach ($messages as $m): ?>
                    <li class="list-group-item"><?php echo htmlspecialchars($m); ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="login.php" class="btn btn-primary">Go to Login</a>
            <p class="text-muted mt-3 small">Delete setup.php now for security/cleanliness.</p>
        </div>
    </div>
</div>
</body>
</html>
